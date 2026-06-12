<?php

namespace App\Controllers\Admin;

use App\Models\ProspectModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Prospects extends Controller
{
    private const VALID_STATUSES = ['new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost'];

    public function index(): string
    {
        $model  = new ProspectModel();
        $filter = $this->request->getGet('status') ?? 'all';

        $query = $model->orderBy('created_at', 'DESC');
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        return view('admin/prospects/index', [
            'prospects'    => $query->findAll(),
            'statusCounts' => $model->countByStatus(),
            'activeFilter' => $filter,
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

        return view('admin/prospects/show', ['prospect' => $prospect]);
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
                       ->orderBy('reviews_count', 'DESC')
                       ->orderBy('created_at', 'ASC');

        if (! empty($skipped)) {
            $query->whereNotIn('id', $skipped);
        }

        $prospect  = $query->first();
        $total     = (new ProspectModel())->where('status', 'new')->countAllResults();

        return view('admin/prospects/queue', [
            'prospect' => $prospect,
            'total'    => $total,
            'skipped'  => count($skipped),
        ]);
    }

    public function queueAction(int $id)
    {
        $action = $this->request->getPost('action');

        if ($action === 'contacted') {
            (new ProspectModel())->update($id, ['status' => 'contacted']);
            $skipped = array_values(array_filter(
                session()->get('queue_skipped') ?? [],
                fn($s) => (int) $s !== $id
            ));
            session()->set('queue_skipped', $skipped);

        } elseif ($action === 'disqualify') {
            (new ProspectModel())->update($id, ['status' => 'lost']);

        } elseif ($action === 'skip') {
            $skipped   = session()->get('queue_skipped') ?? [];
            $skipped[] = $id;
            session()->set('queue_skipped', array_unique($skipped));
        }

        return redirect()->to('/admin/prospects/queue');
    }

    public function queueClearSkips()
    {
        session()->remove('queue_skipped');
        return redirect()->to('/admin/prospects/queue');
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
