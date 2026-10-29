<?php

namespace App\Controllers\Admin;

use App\Models\SettingModel;
use App\Libraries\PlacesClient;
use App\Models\ProspectModel;
use CodeIgniter\Controller;

class GoogleMapsImport extends Controller
{
    private SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    public function index(): string
    {
        return view('admin/google-maps-import/index', [
            'title'      => 'Captação Google Maps',
            'apiKey'     => $this->settings->secret('gmaps_api_key', 'GMAPS_API_KEY'),
            'apiKeyHint' => SettingModel::mask($this->settings->secret('gmaps_api_key', 'GMAPS_API_KEY')),
            'fromEnv'    => (string) env('GMAPS_API_KEY', '') !== '',
            'minRating'  => $this->settings->get('gmaps_min_rating', '4.0'),
            'minReviews' => $this->settings->get('gmaps_min_reviews', '50'),
            'searches'   => $this->settings->get('gmaps_searches', implode("\n", $this->defaultSearches())),
        ]);
    }

    public function save()
    {
        $key = trim((string) $this->request->getPost('api_key'));
        if ($key !== '') { // campo vazio = manter a chave atual
            $this->settings->put('gmaps_api_key', $key);
        }
        $this->settings->put('gmaps_min_rating',  $this->request->getPost('min_rating'));
        $this->settings->put('gmaps_min_reviews', $this->request->getPost('min_reviews'));
        $this->settings->put('gmaps_searches',    trim($this->request->getPost('searches')));

        return redirect()->to('/admin/google-maps-import')->with('success', 'Configurações salvas.');
    }

    public function run(): void
    {
        $apiKey     = $this->settings->secret('gmaps_api_key', 'GMAPS_API_KEY');
        $minRating  = (float) $this->settings->get('gmaps_min_rating', 4.0);
        $minReviews = (int)   $this->settings->get('gmaps_min_reviews', 50);
        $rawSearches = $this->settings->get('gmaps_searches', '');
        $searches   = array_values(array_filter(array_map('trim', explode("\n", $rawSearches))));

        @ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        set_time_limit(600);
        ignore_user_abort(true);

        $send = function (string $type, array $data): void {
            echo 'data: ' . json_encode(['type' => $type, 'data' => $data]) . "\n\n";
            ob_flush();
            flush();
        };

        $emptySkips = ['com_site' => 0, 'rating_baixo' => 0, 'poucos_reviews' => 0, 'sem_telefone' => 0, 'duplicado' => 0];

        if (! $apiKey) {
            $send('error', ['message' => 'API Key não configurada. Salve as configurações primeiro.']);
            $send('done', ['imported' => 0, 'skipped' => $emptySkips]);
            exit;
        }

        $places        = new PlacesClient($apiKey);
        $prospectModel = new ProspectModel();
        $imported      = 0;
        $skipped       = $emptySkips;
        $seenPlaceIds  = [];

        // Termos do Google: só o place_id é gravado. Nota/telefone/site são usados aqui apenas
        // para filtrar e descartados; nome e telefone voltam a ser buscados ao vivo na fila.
        foreach ($searches as $query) {
            $send('search', ['query' => $query]);

            $candidates = [];
            $token      = null;
            try {
                for ($page = 0; $page < 3; $page++) { // a API devolve no máximo 60 resultados por busca
                    $res        = $places->searchText($query, $token);
                    $candidates = array_merge($candidates, $res['places']);
                    $token      = $res['nextPageToken'];
                    if (! $token) break;
                    usleep(300000);
                }
            } catch (\RuntimeException $e) {
                $send('error', ['message' => $e->getMessage()]);
                continue;
            }
            $send('count', ['count' => count($candidates)]);

            foreach ($candidates as $place) {
                $placeId = $place['id'] ?? '';
                if (! $placeId || isset($seenPlaceIds[$placeId])) continue;
                $seenPlaceIds[$placeId] = true;

                $rating  = (float) ($place['rating'] ?? 0);
                $reviews = (int)   ($place['userRatingCount'] ?? 0);

                if ($rating  < $minRating)  { $skipped['rating_baixo']++;   continue; }
                if ($reviews < $minReviews) { $skipped['poucos_reviews']++; continue; }
                if (! empty($place['websiteUri'])) { $skipped['com_site']++; continue; }
                if (trim($place['nationalPhoneNumber'] ?? '') === '') { $skipped['sem_telefone']++; continue; }

                if ($prospectModel->where('google_place_id', $placeId)->countAllResults() > 0) {
                    $skipped['duplicado']++;
                    continue;
                }

                $label = 'Lead Google Maps …' . substr($placeId, -6);
                $prospectModel->insert([
                    'name'            => $label,
                    'interest'        => 'site',
                    'source'          => 'google_maps',
                    'status'          => 'new',
                    'google_place_id' => $placeId,
                    'contact_source'  => ProspectModel::SOURCE_GOOGLE_MAPS,
                ]);

                $imported++;
                $send('imported', ['name' => $label, 'rating' => $rating, 'reviews' => $reviews]);
            }
        }

        $send('done', ['imported' => $imported, 'skipped' => $skipped]);
        exit;
    }

    private function defaultSearches(): array
    {
        return [
            'restaurante Pinheiros São Paulo',
            'hamburgueria Pinheiros São Paulo',
            'pizzaria artesanal Pinheiros São Paulo',
            'café gourmet Pinheiros São Paulo',
            'padaria boutique Pinheiros São Paulo',
            'bistrô Pinheiros São Paulo',
            'restaurante Vila Madalena São Paulo',
            'hamburgueria Vila Madalena São Paulo',
            'café Vila Madalena São Paulo',
            'restaurante Itaim Bibi São Paulo',
            'bistrô Itaim Bibi São Paulo',
            'hamburgueria Itaim Bibi São Paulo',
            'restaurante Moema São Paulo',
            'padaria boutique Moema São Paulo',
            'restaurante Jardins São Paulo',
            'restaurante Vila Olímpia São Paulo',
        ];
    }
}
