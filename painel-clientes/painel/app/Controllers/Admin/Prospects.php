<?php

namespace App\Controllers\Admin;

use App\Libraries\GroqMessageGenerator;
use App\Libraries\PlacesClient;
use App\Models\ProspectCallModel;
use App\Models\ProspectModel;
use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Prospects extends Controller
{
    /** Rodapé obrigatório em toda abordagem fria: identifica a origem e oferece descadastro simples. */
    public const OPT_OUT_FOOTER = 'Se preferir não receber mais mensagens nossas, é só responder SAIR.';

    private const DEFAULT_WA_DAILY_LIMIT = 20;

    /** Limite diário de abordagens por WhatsApp (0 = sem limite). Reduz risco de banimento do número. */
    private function waDailyLimit(): int
    {
        return max(0, (int) (new SettingModel())->get('whatsapp_daily_limit', self::DEFAULT_WA_DAILY_LIMIT));
    }

    /** Nome (e sobrenome) de quem está logado, como cadastrado no perfil. Usado na assinatura das abordagens. */
    public static function senderName(): string
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) session()->get('user_name')));

        return $name !== '' ? $name : 'Eu';
    }

    /** Nome genérico (ex.: "Administrador") ou só um nome sem sobrenome: vale avisar para completar no perfil. */
    public static function senderNameIsWeak(): bool
    {
        $name = mb_strtolower(self::senderName());

        return in_array($name, ['eu', 'admin', 'administrador', 'administrator'], true) || ! str_contains($name, ' ');
    }

    private function waSentToday(): int
    {
        return (new ProspectModel())->where('whatsapp_followup_at >=', date('Y-m-d 00:00:00'))->countAllResults();
    }

    private const VALID_STATUSES = ['new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost', 'phone_followup', 'whatsapp_followup'];

    public function index(): string
    {
        $filter  = $this->request->getGet('status') ?? 'all';
        $perPage = (int) ($this->request->getGet('per_page') ?? session()->get('prospects_per_page') ?? 25);
        if (! in_array($perPage, [10, 25, 50, 100, 200], true)) {
            $perPage = 25;
        }
        if ($this->request->getGet('per_page')) {
            session()->set('prospects_per_page', $perPage);
        }

        $statusCounts = (new ProspectModel())->countByStatus();

        $model = new ProspectModel();
        if ($filter !== 'all') {
            $model->where('status', $filter);
        }
        $prospects = $model->orderBy('created_at', 'DESC')->paginate($perPage);
        $pager     = $model->pager;

        return view('admin/prospects/index', [
            'prospects'    => $prospects,
            'statusCounts' => $statusCounts,
            'activeFilter' => $filter,
            'pager'        => $pager,
            'perPage'      => $perPage,
        ]);
    }

    public function create(): string
    {
        return view('admin/prospects/show', ['prospect' => null]);
    }

    public function store()
    {
        $data = $this->extractFields();

        if ($data['name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nome é obrigatório.');
        }
        if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'E-mail inválido.');
        }

        $data['status'] = 'new';
        (new ProspectModel())->insert($data);

        return redirect()->to('/admin/prospects')->with('success', 'Prospecto criado com sucesso.');
    }

    public function show(int $id): string
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }

        $calls = (new ProspectCallModel())
            ->where('prospect_id', $id)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $liveError = null;
        $live      = $this->withLiveData($prospect, $liveError);

        return view('admin/prospects/show', [
            'prospect'  => $prospect,
            'calls'     => $calls,
            'live'      => ! empty($live['_live']) ? $live : null,
            'liveError' => $liveError,
        ]);
    }

    public function update(int $id)
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }

        $data = $this->extractFields();
        $data['status'] = $this->request->getPost('status') ?? $prospect['status'];

        if ($data['name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nome é obrigatório.');
        }
        if ($data['email'] !== '' && ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'E-mail inválido.');
        }

        (new ProspectModel())->update($id, $data);

        return redirect()->to("/admin/prospects/{$id}")->with('success', 'Prospecto atualizado.');
    }

    public function updateStatus(int $id)
    {
        $status = $this->request->getPost('status');

        if (! in_array($status, self::VALID_STATUSES, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Status inválido.']);
        }

        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prospecto não encontrado.']);
        }

        if (! empty($prospect['opt_out_at']) && $status !== 'lost') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Este contato pediu para não ser contatado (opt-out). Só é possível mantê-lo como perdido.',
            ]);
        }

        (new ProspectModel())->update($id, ['status' => $status]);

        return $this->response->setJSON(['success' => true, 'status' => $status]);
    }

    public function delete(int $id)
    {
        (new ProspectModel())->delete($id);
        return redirect()->to('/admin/prospects')->with('success', 'Prospecto removido.');
    }

    public function convertToClient(int $id)
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }

        if (empty($prospect['email'])) {
            return redirect()->back()->with('error', 'Prospecto sem e-mail. Adicione um e-mail antes de converter.');
        }

        $userModel = new UserModel();
        if ($userModel->where('email', $prospect['email'])->first()) {
            return redirect()->back()->with('error', 'Já existe um cliente cadastrado com este e-mail.');
        }

        $tempPassword = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 10);

        $clientId = $userModel->insert([
            'name'     => $prospect['name'],
            'email'    => $prospect['email'],
            'phone'    => $prospect['phone'],
            'password' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'role'     => 'client',
            'active'   => true,
        ]);

        (new ProspectModel())->update($id, [
            'status' => 'won',
            'notes'  => trim(($prospect['notes'] ?? '') . "\n\n[Convertido em cliente #$clientId]"),
        ]);

        return redirect()->to("/admin/clients/{$clientId}")
            ->with('success', "Cliente criado com sucesso! Senha temporária: {$tempPassword}")
            ->with('temp_password', $tempPassword);
    }

    public function queue(): string
    {
        $skipped = session()->get('queue_skipped') ?? [];
        $model   = new ProspectModel();

        $query = $model->where('status', 'new')
                       ->where('opt_out_at', null)
                       ->orderBy('reviews_count', 'DESC')
                       ->orderBy('created_at', 'ASC');

        if (! empty($skipped)) {
            $query->whereNotIn('id', $skipped);
        }

        $prospect  = $query->first();
        $liveError = null;
        $prospect  = $this->withLiveData($prospect, $liveError);
        $total     = (new ProspectModel())->where('status', 'new')->where('opt_out_at', null)->countAllResults();

        return view('admin/prospects/queue', [
            'prospect' => $prospect,
            'total'    => $total,
            'skipped'  => count($skipped),
            'liveError' => $liveError,
            'waLimit'   => $this->waDailyLimit(),
            'waSent'    => $this->waSentToday(),
        ]);
    }

    public function queueAction(int $id)
    {
        $action = $this->request->getPost('action');

        $removeFromSkipped = function (int $id): void {
            $skipped = array_values(array_filter(
                session()->get('queue_skipped') ?? [],
                fn($s) => (int) $s !== $id
            ));
            session()->set('queue_skipped', $skipped);
        };

        $current = (new ProspectModel())->find($id);
        if ($current && ! empty($current['opt_out_at']) && $action !== 'opt_out') {
            return redirect()->to('/admin/prospects/queue')->with('error', 'Este contato pediu para não ser contatado.');
        }
        $firstContact = ($current && empty($current['first_contact_at'])) ? date('Y-m-d H:i:s') : null;

        if ($action === 'contacted') {
            $limit = $this->waDailyLimit();
            if ($limit > 0 && $this->waSentToday() >= $limit) {
                return redirect()->to('/admin/prospects/queue')
                    ->with('error', "Limite diário de {$limit} abordagens por WhatsApp atingido. Continue amanhã ou ajuste o limite em Configurações de IA.");
            }
            $data = [
                'status'                => 'whatsapp_followup',
                'whatsapp_followup_at'  => date('Y-m-d H:i:s'),
            ];
            if ($firstContact) { $data['first_contact_at'] = $firstContact; }
            (new ProspectModel())->update($id, $data);
            $removeFromSkipped($id);

        } elseif ($action === 'no_whatsapp') {
            $data = [
                'status'             => 'phone_followup',
                'phone_followup_at'  => date('Y-m-d H:i:s'),
            ];
            if ($firstContact) { $data['first_contact_at'] = $firstContact; }
            (new ProspectModel())->update($id, $data);
            $removeFromSkipped($id);

        } elseif ($action === 'opt_out') {
            $this->applyOptOut($id);
            $removeFromSkipped($id);

        } elseif ($action === 'disqualify') {
            (new ProspectModel())->update($id, ['status' => 'lost']);

        } elseif ($action === 'skip') {
            $skipped   = session()->get('queue_skipped') ?? [];
            $skipped[] = $id;
            session()->set('queue_skipped', array_unique($skipped));
        }

        return redirect()->to('/admin/prospects/queue');
    }

    /**
     * Leads do Google Maps guardam só o place_id (termos do Google). Nome, telefone, nota e link
     * vêm da API na hora de exibir e NÃO são gravados. Retorna o prospecto com os dados ao vivo
     * mesclados em memória (flag `_live`); em caso de falha, `$error` explica o motivo.
     */
    private function withLiveData(?array $prospect, ?string &$error = null): ?array
    {
        if (! $prospect || ! ProspectModel::isLivePlace($prospect)) {
            return $prospect;
        }

        $apiKey = (new SettingModel())->secret('gmaps_api_key', 'GMAPS_API_KEY');
        try {
            $d = (new PlacesClient($apiKey))->details($prospect['google_place_id']);
        } catch (\RuntimeException $e) {
            $error = $e->getMessage();
            return $prospect;
        }

        if ($d === null) {
            $error = 'Este lugar não existe mais no Google Maps. Descarte o lead.';
            return $prospect;
        }

        $prospect['name']          = $d['displayName']['text'] ?? $prospect['name'];
        $prospect['phone']         = $d['nationalPhoneNumber'] ?? null;
        $prospect['rating']        = $d['rating'] ?? null;
        $prospect['reviews_count'] = $d['userRatingCount'] ?? null;
        $prospect['maps_url']      = $d['googleMapsUri'] ?? PlacesClient::mapsUrl($prospect['google_place_id']);
        $prospect['_live']         = true;

        return $prospect;
    }

    /** "Salvar contato": a pessoa respondeu; nome e telefone passam a ser dados do seu relacionamento. */
    public function releaseContact(int $id)
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect || ! ProspectModel::isLivePlace($prospect)) {
            return redirect()->back()->with('error', 'Nada a salvar para este prospecto.');
        }

        $live = $this->withLiveData($prospect, $error);
        if (empty($live['_live'])) {
            return redirect()->back()->with('error', $error ?: 'Não foi possível buscar os dados no Google.');
        }

        (new ProspectModel())->update($id, [
            'name'               => $live['name'],
            'phone'              => $live['phone'],
            'places_released_at' => date('Y-m-d H:i:s'),
            'notes'              => trim(($prospect['notes'] ?? '') . "\n\n[" . date('d/m/Y H:i') . '] Contato salvo após resposta do lead (dados do Google Maps deixam de ser consultados).'),
        ]);

        return redirect()->back()->with('success', 'Nome e telefone salvos na ficha.');
    }

    /** Botão "Não contatar" na ficha do prospecto. */
    public function optOut(int $id)
    {
        if (! (new ProspectModel())->find($id)) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }
        $this->applyOptOut($id);

        return redirect()->back()->with('success', 'Contato marcado como "não contatar". Ele não aparecerá mais nas filas de abordagem.');
    }

    /** Marca opt-out: registra data, encerra o funil como perdido e mantém o telefone na lista de supressão. */
    private function applyOptOut(int $id): void
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return;
        }
        $stamp = date('d/m/Y H:i');
        (new ProspectModel())->update($id, [
            'opt_out_at' => $prospect['opt_out_at'] ?: date('Y-m-d H:i:s'),
            'status'     => 'lost',
            'notes'      => trim(($prospect['notes'] ?? '') . "\n\n[{$stamp}] Pediu para não ser contatado (opt-out)."),
        ]);
    }

    public function queueClearSkips()
    {
        session()->remove('queue_skipped');
        return redirect()->to('/admin/prospects/queue');
    }

    public function generateMessage(int $id)
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return $this->response->setJSON(['success' => false, 'error' => 'Prospecto não encontrado.']);
        }

        $prospect = $this->withLiveData($prospect, $liveError);
        if (ProspectModel::isLivePlace($prospect) && empty($prospect['_live'])) {
            return $this->response->setJSON(['success' => false, 'error' => $liveError ?: 'Não foi possível buscar os dados do lead no Google.']);
        }

        $apiKey    = (new SettingModel())->secret('groq_api_key', 'GROQ_API_KEY');
        $adminName = self::senderName();
        $saudacao  = $this->request->getPost('saudacao') ?: 'boa tarde';

        try {
            $generator = new GroqMessageGenerator($apiKey);
            $message   = $generator->generate($prospect, $adminName, $saudacao);
            if (stripos($message, 'SAIR') === false) {
                $message .= "\n\n" . self::OPT_OUT_FOOTER;
            }

            return $this->response->setJSON(['success' => true, 'message' => $message]);
        } catch (\RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function phoneFollowup(): string
    {
        [$prospects, $lastCalls] = $this->followupList('phone_followup', 'phone_followup_at');

        return view('admin/prospects/phone-followup', [
            'prospects' => $prospects,
            'lastCalls' => $lastCalls,
        ]);
    }

    public function whatsappFollowup(): string
    {
        [$prospects, $lastCalls] = $this->followupList('whatsapp_followup', 'whatsapp_followup_at');

        return view('admin/prospects/whatsapp-followup', [
            'prospects' => $prospects,
            'lastCalls' => $lastCalls,
        ]);
    }

    public function archive(): string
    {
        $filter  = $this->request->getGet('status') ?? 'all';
        $query   = trim($this->request->getGet('q') ?? '');
        $perPage = (int) ($this->request->getGet('per_page') ?? session()->get('archive_per_page') ?? 25);
        if (! in_array($perPage, [10, 25, 50, 100, 200], true)) {
            $perPage = 25;
        }
        if ($this->request->getGet('per_page')) {
            session()->set('archive_per_page', $perPage);
        }

        $model = (new ProspectModel())->where('status !=', 'new');

        if ($filter !== 'all') {
            $model->where('status', $filter);
        }

        if ($query !== '') {
            $model->groupStart()
                  ->like('name', $query)
                  ->orLike('company', $query)
                  ->orLike('phone', $query)
                  ->orLike('email', $query)
                  ->groupEnd();
        }

        $prospects = $model->orderBy('updated_at', 'DESC')->paginate($perPage, 'archive');
        $pager     = $model->pager;

        $lastCalls = $this->lastInteractionsFor(array_column($prospects, 'id'));

        return view('admin/prospects/archive', [
            'prospects'    => $prospects,
            'lastCalls'    => $lastCalls,
            'activeFilter' => $filter,
            'query'        => $query,
            'pager'        => $pager,
            'perPage'      => $perPage,
        ]);
    }

    public function logCall(int $id)
    {
        $notes = trim($this->request->getPost('notes') ?? '');

        (new ProspectCallModel())->insert([
            'prospect_id' => $id,
            'notes'       => $notes ?: null,
        ]);

        return redirect()->back()->with('success', 'Interação registrada.');
    }

    public function phoneFollowupExport(): void
    {
        $this->exportFollowupCsv('phone_followup', 'phone_followup_at', 'follow-up-telefone.csv');
    }

    public function whatsappFollowupExport(): void
    {
        $this->exportFollowupCsv('whatsapp_followup', 'whatsapp_followup_at', 'follow-up-whatsapp.csv');
    }

    /**
     * Busca prospects num status de follow-up + a última interação registrada de cada um.
     * @return array{0: array, 1: array} [$prospects, $lastCalls indexado por prospect_id]
     */
    private function followupList(string $status, string $dateField): array
    {
        $prospects = (new ProspectModel())
            ->where('status', $status)
            ->orderBy($dateField, 'ASC')
            ->findAll();

        return [$prospects, $this->lastInteractionsFor(array_column($prospects, 'id'))];
    }

    /** @return array indexado por prospect_id com a interação mais recente de cada um */
    private function lastInteractionsFor(array $prospectIds): array
    {
        if (empty($prospectIds)) {
            return [];
        }

        $calls = (new ProspectCallModel())
            ->whereIn('prospect_id', $prospectIds)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        $lastCalls = [];
        foreach ($calls as $call) {
            if (! isset($lastCalls[$call['prospect_id']])) {
                $lastCalls[$call['prospect_id']] = $call;
            }
        }

        return $lastCalls;
    }

    private function exportFollowupCsv(string $status, string $dateField, string $filename): void
    {
        [$prospects, $lastCalls] = $this->followupList($status, $dateField);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM pro Excel reconhecer UTF-8
        fputcsv($out, ['Nome', 'Telefone', 'Empresa', 'Nota', 'Avaliações', 'Entrada na lista', 'Última interação', 'Observação'], ';');

        foreach ($prospects as $p) {
            $lastCall = $lastCalls[$p['id']] ?? null;
            fputcsv($out, [
                $p['name'],
                $p['phone'],
                $p['company'],
                $p['rating'],
                $p['reviews_count'],
                $p[$dateField] ? fmt_dt($p[$dateField], 'd/m/Y') : '',
                $lastCall ? fmt_dt($lastCall['created_at'], 'd/m/Y') : '',
                $lastCall['notes'] ?? '',
            ], ';');
        }

        fclose($out);
        exit;
    }

    public function importForm(): string
    {
        return view('admin/prospects/import');
    }

    public function importProcess()
    {
        $file = $this->request->getFile('csv_file');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Arquivo inválido ou não enviado.');
        }

        $minRating  = (float) ($this->request->getPost('min_rating')  ?? 4.0);
        $minReviews = (int)   ($this->request->getPost('min_reviews') ?? 50);

        $handle = fopen($file->getTempName(), 'r');
        // Remove UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $rawHeaders = fgetcsv($handle);
        if (! $rawHeaders) {
            fclose($handle);
            return redirect()->back()->with('error', 'CSV vazio ou formato inválido.');
        }

        $headers = array_map(fn($h) => strtolower(trim($h)), $rawHeaders);
        $col     = array_flip($headers);

        // Map Outscraper column name variations
        $iName    = $col['name']    ?? $col['title']           ?? null;
        $iPhone   = $col['phone']   ?? $col['phone_1']         ?? $col['phone_number'] ?? null;
        $iEmail   = $col['email']   ?? $col['email_1']         ?? null;
        $iSite    = $col['site']    ?? $col['website']         ?? null;
        $iRating  = $col['rating']  ?? $col['google_rating']   ?? null;
        $iReviews = $col['reviews'] ?? $col['reviews_count']   ?? $col['number_of_reviews'] ?? null;
        $iMaps    = $col['place_link'] ?? $col['link']         ?? $col['url'] ?? $col['google_maps_url'] ?? null;

        $get = fn(?int $idx, array $row): string => ($idx !== null && isset($row[$idx])) ? trim($row[$idx]) : '';

        $model    = new ProspectModel();
        $imported = 0;
        $reasons  = ['com_site' => 0, 'rating_baixo' => 0, 'poucos_reviews' => 0, 'duplicado' => 0, 'sem_nome' => 0];

        while (($row = fgetcsv($handle)) !== false) {
            $name    = $get($iName, $row);
            $phone   = $get($iPhone, $row);
            $email   = $get($iEmail, $row);
            $site    = $get($iSite, $row);
            $rating  = (float) $get($iRating, $row);
            $reviews = (int)   $get($iReviews, $row);
            $mapsUrl = $get($iMaps, $row);

            if ($name === '')       { $reasons['sem_nome']++;      continue; }
            if ($site !== '')       { $reasons['com_site']++;      continue; }
            if ($rating < $minRating)  { $reasons['rating_baixo']++;  continue; }
            if ($reviews < $minReviews){ $reasons['poucos_reviews']++; continue; }

            // Dedup por email (quando disponível)
            if ($email !== '' && $model->where('email', $email)->countAllResults() > 0) {
                $reasons['duplicado']++; continue;
            }

            $model->insert([
                'name'          => $name,
                'email'         => $email ?: null,
                'phone'         => $phone ?: null,
                'interest'      => 'site',
                'source'        => 'google_maps',
                'status'        => 'new',
                'rating'        => $rating > 0 ? $rating : null,
                'reviews_count' => $reviews > 0 ? $reviews : null,
                'maps_url'      => $mapsUrl ?: null,
                'contact_source' => ProspectModel::SOURCE_GOOGLE_MAPS,
            ]);
            $imported++;
        }

        fclose($handle);

        $skipped = array_sum($reasons);
        $details = [];
        if ($reasons['com_site'])      $details[] = "{$reasons['com_site']} com site";
        if ($reasons['rating_baixo'])  $details[] = "{$reasons['rating_baixo']} avaliação < {$minRating}";
        if ($reasons['poucos_reviews'])$details[] = "{$reasons['poucos_reviews']} < {$minReviews} reviews";
        if ($reasons['duplicado'])     $details[] = "{$reasons['duplicado']} duplicado";

        $msg = "{$imported} prospecto(s) importado(s).";
        if ($skipped > 0) {
            $msg .= " {$skipped} ignorado(s): " . implode(', ', $details) . '.';
        }

        return redirect()->to('/admin/prospects')->with('success', $msg);
    }

    private function extractFields(): array
    {
        $rating  = $this->request->getPost('rating');
        $reviews = $this->request->getPost('reviews_count');

        return [
            'name'          => trim($this->request->getPost('name')    ?? ''),
            'email'         => trim($this->request->getPost('email')   ?? ''),
            'phone'         => trim($this->request->getPost('phone')   ?? '') ?: null,
            'company'       => trim($this->request->getPost('company') ?? '') ?: null,
            'interest'      => $this->request->getPost('interest') ?? 'other',
            'source'        => $this->request->getPost('source')   ?? 'other',
            'notes'         => trim($this->request->getPost('notes')   ?? '') ?: null,
            'rating'        => ($rating !== null && $rating !== '') ? (float) $rating : null,
            'reviews_count' => ($reviews !== null && $reviews !== '') ? (int) $reviews : null,
            'maps_url'      => trim($this->request->getPost('maps_url') ?? '') ?: null,
        ];
    }
}
