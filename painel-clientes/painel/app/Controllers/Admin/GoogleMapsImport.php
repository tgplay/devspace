<?php

namespace App\Controllers\Admin;

use App\Models\SettingModel;
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
            'apiKey'     => $this->settings->get('gmaps_api_key', ''),
            'minRating'  => $this->settings->get('gmaps_min_rating', '4.0'),
            'minReviews' => $this->settings->get('gmaps_min_reviews', '50'),
            'searches'   => $this->settings->get('gmaps_searches', implode("\n", $this->defaultSearches())),
        ]);
    }

    public function save()
    {
        $this->settings->set('gmaps_api_key',     $this->request->getPost('api_key'));
        $this->settings->set('gmaps_min_rating',  $this->request->getPost('min_rating'));
        $this->settings->set('gmaps_min_reviews', $this->request->getPost('min_reviews'));
        $this->settings->set('gmaps_searches',    trim($this->request->getPost('searches')));

        return redirect()->to('/admin/google-maps-import')->with('success', 'Configurações salvas.');
    }

    public function run(): void
    {
        $apiKey     = $this->settings->get('gmaps_api_key', '');
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

        if (! $apiKey) {
            $send('error', ['message' => 'API Key não configurada. Salve as configurações primeiro.']);
            $send('done', ['imported' => 0, 'skipped' => ['com_site' => 0, 'rating_baixo' => 0, 'poucos_reviews' => 0, 'sem_telefone' => 0, 'duplicado' => 0]]);
            exit;
        }

        $db            = \Config\Database::connect();
        $prospectModel = new ProspectModel();
        $imported      = 0;
        $skipped       = ['com_site' => 0, 'rating_baixo' => 0, 'poucos_reviews' => 0, 'sem_telefone' => 0, 'duplicado' => 0];
        $seenPlaceIds  = [];

        foreach ($searches as $query) {
            $send('search', ['query' => $query]);

            $candidates = $this->textSearch($query, $apiKey);
            $send('count', ['count' => count($candidates)]);

            foreach ($candidates as $place) {
                $placeId = $place['place_id'] ?? '';
                if (! $placeId || isset($seenPlaceIds[$placeId])) continue;
                $seenPlaceIds[$placeId] = true;

                $rating  = (float) ($place['rating'] ?? 0);
                $reviews = (int)   ($place['user_ratings_total'] ?? 0);

                if ($rating  < $minRating)  { $skipped['rating_baixo']++;  continue; }
                if ($reviews < $minReviews) { $skipped['poucos_reviews']++; continue; }

                usleep(100000);
                $details = $this->placeDetails($placeId, $apiKey);

                if (! empty($details['website'])) { $skipped['com_site']++; continue; }

                $phone = trim($details['formatted_phone_number'] ?? '');
                if (! $phone) { $skipped['sem_telefone']++; continue; }

                $name    = $details['name'] ?? ($place['name'] ?? '');
                $mapsUrl = $details['url'] ?? '';

                $exists = $db->query(
                    'SELECT 1 FROM prospects WHERE phone = ? OR LOWER(name) = LOWER(?) LIMIT 1',
                    [$phone, $name]
                )->getNumRows() > 0;

                if ($exists) { $skipped['duplicado']++; continue; }

                $prospectModel->insert([
                    'name'          => $name,
                    'phone'         => $phone,
                    'interest'      => 'site',
                    'source'        => 'google_maps',
                    'status'        => 'new',
                    'rating'        => $rating,
                    'reviews_count' => $reviews,
                    'maps_url'      => $mapsUrl,
                ]);

                $imported++;
                $send('imported', ['name' => $name, 'rating' => $rating, 'reviews' => $reviews]);
            }
        }

        $send('done', ['imported' => $imported, 'skipped' => $skipped]);
        exit;
    }

    // ── Google Places API ─────────────────────────────────────────────────────

    private function textSearch(string $query, string $apiKey): array
    {
        $results = [];
        $url     = 'https://maps.googleapis.com/maps/api/place/textsearch/json';
        $params  = ['query' => $query, 'language' => 'pt-BR', 'key' => $apiKey];

        while (true) {
            $resp    = $this->httpGet($url, $params);
            $results = array_merge($results, $resp['results'] ?? []);

            $token = $resp['next_page_token'] ?? null;
            if (! $token) break;

            sleep(2);
            $params = ['pagetoken' => $token, 'key' => $apiKey];
        }

        return $results;
    }

    private function placeDetails(string $placeId, string $apiKey): array
    {
        $resp = $this->httpGet(
            'https://maps.googleapis.com/maps/api/place/details/json',
            [
                'place_id' => $placeId,
                'fields'   => 'name,formatted_phone_number,website,url',
                'language' => 'pt-BR',
                'key'      => $apiKey,
            ]
        );
        return $resp['result'] ?? [];
    }

    private function httpGet(string $url, array $params): array
    {
        $fullUrl = $url . '?' . http_build_query($params);
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $body    = @file_get_contents($fullUrl, false, $context);
        return $body ? (json_decode($body, true) ?? []) : [];
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
