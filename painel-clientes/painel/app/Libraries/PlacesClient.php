<?php

namespace App\Libraries;

/**
 * Cliente mínimo da Places API (New) — https://places.googleapis.com/v1
 *
 * Termos do Google: só o place_id pode ser armazenado sem prazo. Nome, telefone, nota e demais
 * conteúdos devem ser buscados na hora de exibir e NÃO podem ser gravados no banco.
 * Sempre peça só os campos necessários (X-Goog-FieldMask): o preço depende do campo mais caro.
 */
class PlacesClient
{
    private const BASE = 'https://places.googleapis.com/v1';

    /** Campos usados na busca/importação (tier Enterprise: telefone, site, nota). */
    public const SEARCH_MASK = 'places.id,places.rating,places.userRatingCount,places.websiteUri,places.nationalPhoneNumber,nextPageToken';

    /** Campos usados ao exibir um lead na fila/ficha. */
    public const DETAILS_MASK = 'id,displayName,nationalPhoneNumber,rating,userRatingCount,googleMapsUri,websiteUri';

    public function __construct(private string $apiKey)
    {
    }

    /**
     * @return array{places: list<array>, nextPageToken: ?string}
     * @throws \RuntimeException
     */
    public function searchText(string $query, ?string $pageToken = null, string $mask = self::SEARCH_MASK): array
    {
        $body = ['textQuery' => $query, 'languageCode' => 'pt-BR', 'regionCode' => 'BR', 'pageSize' => 20];
        if ($pageToken) {
            $body['pageToken'] = $pageToken;
        }

        [$code, $data] = $this->request('POST', self::BASE . '/places:searchText', $mask, $body);
        if ($code >= 400) {
            throw new \RuntimeException($this->errorMessage($code, $data));
        }

        return [
            'places'        => $data['places'] ?? [],
            'nextPageToken' => $data['nextPageToken'] ?? null,
        ];
    }

    /**
     * Detalhes de um lugar. Retorna null se o lugar não existe mais (404).
     *
     * @throws \RuntimeException em erro de rede, chave inválida ou cota.
     */
    public function details(string $placeId, string $mask = self::DETAILS_MASK): ?array
    {
        $url = self::BASE . '/places/' . rawurlencode($placeId) . '?languageCode=pt-BR&regionCode=BR';
        [$code, $data] = $this->request('GET', $url, $mask);

        if ($code === 404) {
            return null;
        }
        if ($code >= 400) {
            throw new \RuntimeException($this->errorMessage($code, $data));
        }

        return $data;
    }

    /** Link do Google Maps montado a partir do place_id (permitido: não depende de conteúdo armazenado). */
    public static function mapsUrl(string $placeId): string
    {
        return 'https://www.google.com/maps/place/?q=place_id:' . rawurlencode($placeId);
    }

    /** @return array{0:int, 1:array} */
    private function request(string $method, string $url, string $mask, ?array $body = null): array
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('API Key do Google não configurada.');
        }

        $headers = "X-Goog-Api-Key: {$this->apiKey}\r\nX-Goog-FieldMask: {$mask}\r\n";
        $http    = ['method' => $method, 'timeout' => 15, 'ignore_errors' => true];
        if ($body !== null) {
            $headers        .= "Content-Type: application/json\r\n";
            $http['content'] = json_encode($body);
        }
        $http['header'] = $headers;

        $raw = @file_get_contents($url, false, stream_context_create(['http' => $http]));
        if ($raw === false) {
            throw new \RuntimeException('Não foi possível conectar à Places API.');
        }

        $code = 200;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $h, $m)) {
                $code = (int) $m[1]; // mantém o último (após redirects)
            }
        }

        return [$code, json_decode($raw, true) ?: []];
    }

    private function errorMessage(int $code, array $data): string
    {
        $msg = $data['error']['message'] ?? 'erro desconhecido';
        return "Places API ({$code}): {$msg}";
    }
}
