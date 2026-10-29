<?php

namespace App\Libraries;

use App\Models\ProspectModel;

/**
 * Importa leads da base aberta de CNPJ da Receita Federal (Estabelecimentos*.zip + Municipios.zip).
 * Usada pelo comando `spark prospects:import-cnpj` e pela tela admin "Captação CNPJ".
 *
 * Layout oficial: https://www.gov.br/receitafederal/dados/cnpj-metadados.pdf
 * (CSV sem cabeçalho, separador ";", ISO-8859-1, 30 colunas em Estabelecimentos).
 */
class CnpjImporter
{
    /** Provedores de e-mail gratuitos: e-mail nesses domínios sugere que o negócio não tem site próprio. */
    private const FREE_MAIL = [
        'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com', 'yahoo.com.br', 'uol.com.br', 'bol.com.br',
        'terra.com.br', 'icloud.com', 'live.com', 'msn.com', 'ig.com.br', 'globo.com', 'globomail.com',
        'hotmail.com.br', 'outlook.com.br', 'oi.com.br', 'r7.com',
    ];

    /** @var callable(string $type, array $data): void */
    private $emit;

    /**
     * @param array{municipio: list<string>, cnae: list<string>, bairro?: list<string>, dir: string,
     *              competencia?: string, limit?: int, min_meses?: int, so_celular?: bool,
     *              incluir_dominio_proprio?: bool, dry_run?: bool} $o
     * @param callable(string $type, array $data): void $emit tipos: info, progress, sample, error
     */
    public function __construct(private array $o, callable $emit)
    {
        $this->emit = $emit;
    }

    /** Lista os arquivos da Receita encontrados na pasta. @return array{estab: list<string>, mun: ?string} */
    public static function scanDir(string $dir): array
    {
        $estab = [];
        $mun   = null;
        foreach (is_dir($dir) ? (scandir($dir) ?: []) : [] as $name) {
            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if (! is_file($path)) {
                continue;
            }
            if (preg_match('/^estabelecimentos?\d*\.zip$/i', $name) || preg_match('/ESTABELE$/i', $name)) {
                $estab[] = $path;
            } elseif (preg_match('/^municipios?\.zip$/i', $name) || preg_match('/MUNICCSV$/i', $name)) {
                $mun = $path;
            }
        }
        sort($estab);
        return ['estab' => $estab, 'mun' => $mun];
    }

    /**
     * @return array{imported: int, lines: int, skipped: array<string,int>, samples: list<string>, dry_run: bool}
     * @throws \RuntimeException para erros de configuração (pasta, arquivos, município).
     */
    public function run(): array
    {
        $o          = $this->o;
        $municipios = $o['municipio'] ?? [];
        $cnaes      = array_values(array_filter(array_map(fn($c) => preg_replace('/\D/', '', $c), $o['cnae'] ?? [])));
        if (! $municipios || ! $cnaes) {
            throw new \RuntimeException('Informe ao menos um município e um CNAE.');
        }

        $bairros     = array_map([$this, 'norm'], $o['bairro'] ?? []);
        $dir         = rtrim((string) $o['dir'], '/\\');
        $competencia = (string) ($o['competencia'] ?? 'não informada');
        $limit       = max(1, (int) ($o['limit'] ?? 500));
        $minMeses    = max(0, (int) ($o['min_meses'] ?? 6));
        $soCelular   = ! empty($o['so_celular']);
        $keepOwnDom  = ! empty($o['incluir_dominio_proprio']);
        $dry         = ! empty($o['dry_run']);

        if (! is_dir($dir)) {
            throw new \RuntimeException("Pasta não encontrada: {$dir}");
        }
        $found = self::scanDir($dir);
        if (! $found['estab'] || ! $found['mun']) {
            throw new \RuntimeException("Não encontrei Estabelecimentos*.zip e Municipios.zip em {$dir}.");
        }

        // Códigos de município (código próprio da Receita) a partir do nome
        $wanted = array_map([$this, 'norm'], $municipios);
        $codes  = [];
        $this->eachLine($found['mun'], function (string $line) use ($wanted, &$codes) {
            $f = str_getcsv($this->utf8($line), ';', '"');
            if (count($f) >= 2 && in_array($this->norm($f[1]), $wanted, true)) {
                $codes[trim($f[0])] = $f[1];
            }
            return true;
        });
        if (! $codes) {
            throw new \RuntimeException('Município não encontrado em Municipios.zip. Use o nome sem acento, ex.: "SAO PAULO".');
        }
        ($this->emit)('info', ['message' => 'Municípios: ' . implode(', ', $codes) . ' | CNAE: ' . implode(',', $cnaes)
            . ($bairros ? ' | Bairros: ' . implode(',', $bairros) : '') . ($dry ? ' [simulação]' : '')]);

        $model     = new ProspectModel();
        $db        = \Config\Database::connect();
        $limitDate = (new \DateTimeImmutable("-{$minMeses} months"))->format('Ymd');
        $skip      = ['inativa' => 0, 'cnae' => 0, 'bairro' => 0, 'sem_fantasia' => 0, 'recente' => 0,
                      'sem_telefone' => 0, 'so_celular' => 0, 'dominio_proprio' => 0, 'duplicado' => 0];
        $imported  = 0;
        $lines     = 0;
        $samples   = [];
        $lastBeat  = microtime(true);
        $emit      = $this->emit;
        $stopFn    = $this->o['should_stop'] ?? null; // callable(): bool — pedido de parada vindo da tela
        $stopped   = false;

        foreach ($found['estab'] as $file) {
            ($this->emit)('info', ['message' => 'Lendo ' . basename($file) . ' ...']);
            $this->eachLine($file, function (string $raw) use (
                &$lines, &$lastBeat, &$stopped, $stopFn, $emit, $codes, $cnaes, $bairros, $limitDate, $soCelular, $keepOwnDom, $dry, $limit,
                $competencia, $model, $db, &$skip, &$imported, &$samples
            ) {
                // Pedido de parada (checado a cada 2.000 linhas)
                if ($stopFn && ($lines % 2000) === 0 && $stopFn()) {
                    $stopped = true;

                    return false;
                }

                // Batimento a cada ~5 s: mantém a conexão do navegador viva (timeout do Nginx é 60 s)
                if ((++$lines % 50000) === 0 && (microtime(true) - $lastBeat) >= 5) {
                    $lastBeat = microtime(true);
                    $emit('progress', ['lines' => $lines, 'imported' => $imported]);
                }

                // Descarte rápido por município (campo 21), antes de decodificar/parsear
                $hit = false;
                foreach ($codes as $code => $_) {
                    if (strpos($raw, '"' . $code . '";') !== false || strpos($raw, ';' . $code . ';') !== false) {
                        $hit = true;
                        break;
                    }
                }
                if (! $hit) {
                    return true;
                }

                $f = str_getcsv($this->utf8($raw), ';', '"');
                if (count($f) < 28 || ! isset($codes[trim($f[20])])) {
                    return true;
                }

                if ((int) $f[5] !== 2)                               { $skip['inativa']++; return true; }

                $cnae = preg_replace('/\D/', '', $f[11]);
                $okCnae = false;
                foreach ($cnaes as $p) {
                    if (str_starts_with($cnae, $p)) { $okCnae = true; break; }
                }
                if (! $okCnae)                                       { $skip['cnae']++; return true; }

                if ($bairros && ! in_array($this->norm($f[17]), $bairros, true)) { $skip['bairro']++; return true; }

                $fantasia = trim($f[4]);
                if ($fantasia === '')                                { $skip['sem_fantasia']++; return true; }

                $inicio = preg_replace('/\D/', '', $f[10]);
                if (strlen($inicio) !== 8 || $inicio > $limitDate)   { $skip['recente']++; return true; }

                $isMobile = false;
                $phone = $this->pickPhone([[$f[21], $f[22]], [$f[23], $f[24]]], $soCelular, $isMobile);
                if ($phone === null) {
                    $skip[$soCelular && $this->hasAnyPhone($f) ? 'so_celular' : 'sem_telefone']++;
                    return true;
                }

                // E-mail: domínio próprio => provável site; contabilidade => e-mail não é do negócio
                $email = strtolower(trim($f[27] ?? ''));
                $keepEmail = null;
                if ($email !== '' && str_contains($email, '@')) {
                    $domain = substr(strrchr($email, '@'), 1);
                    if (in_array($domain, self::FREE_MAIL, true)) {
                        $keepEmail = $email;
                    } elseif (! preg_match('/contab|contador|contabil|assessori|escritorio/i', $domain)) {
                        if (! $keepOwnDom) { $skip['dominio_proprio']++; return true; }
                    }
                }

                $cnpj = preg_replace('/\D/', '', $f[0] . $f[1] . $f[2]);
                if (strlen($cnpj) !== 14)                            { return true; }

                $exists = $db->query('SELECT 1 FROM prospects WHERE cnpj = ? OR phone = ? LIMIT 1', [$cnpj, $phone])->getNumRows() > 0;
                if ($exists)                                         { $skip['duplicado']++; return true; }

                $name    = mb_convert_case(mb_strtolower($fantasia), MB_CASE_TITLE);
                $bairro  = mb_convert_case(mb_strtolower(trim($f[17])), MB_CASE_TITLE);
                $cidade  = mb_convert_case(mb_strtolower($codes[trim($f[20])]), MB_CASE_TITLE);
                $company = trim($bairro . ' – ' . $cidade . '/' . trim($f[19]), ' –/');
                $addr    = trim(implode(' ', array_filter([trim($f[13]), trim($f[14]), trim($f[15])])));
                $notes   = 'CNPJ ' . $this->fmtCnpj($cnpj) . ' · CNAE ' . $cnae
                         . ($addr !== '' ? ' · ' . mb_convert_case(mb_strtolower($addr), MB_CASE_TITLE) : '')
                         . ' · Aberta em ' . substr($inicio, 4, 2) . '/' . substr($inicio, 0, 4)
                         . ($isMobile ? ' · Celular' : ' · Telefone fixo');

                $sample = "{$name} | {$phone} | {$company}" . ($keepEmail ? " | {$keepEmail}" : '');
                if (count($samples) < 8) {
                    $samples[] = $sample;
                }

                if (! $dry) {
                    $model->insert([
                        'name'           => $name,
                        'phone'          => $phone,
                        'email'          => $keepEmail,
                        'company'        => $company,
                        'interest'       => 'site',
                        'source'         => 'cnpj',
                        'status'         => 'new',
                        'cnpj'           => $cnpj,
                        'contact_source' => 'Base aberta de CNPJ da Receita Federal (competência ' . $competencia . ')',
                        'notes'          => $notes,
                    ]);
                }
                $imported++;
                $emit('sample', ['text' => $sample, 'imported' => $imported]);

                return $imported < $limit; // false interrompe a leitura
            });

            if ($stopped) {
                ($this->emit)('info', ['message' => 'Importação interrompida a pedido.']);
                break;
            }
            if ($imported >= $limit) {
                ($this->emit)('info', ['message' => "Limite de {$limit} atingido."]);
                break;
            }
        }

        return ['imported' => $imported, 'lines' => $lines, 'skipped' => $skip, 'samples' => $samples, 'dry_run' => $dry, 'stopped' => $stopped];
    }

    // ── helpers ───────────────────────────────────────────────────────────────

    /** Divide "a, b, c" em lista limpa. */
    public static function csv(mixed $v): array
    {
        if (! is_string($v) || trim($v) === '') {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode(',', $v)), fn($x) => $x !== ''));
    }

    /** Sem acento, maiúsculo, espaços normalizados. */
    private function norm(string $s): string
    {
        $s = $this->utf8Safe($s);
        $t = function_exists('transliterator_transliterate')
            ? transliterator_transliterate('Any-Latin; Latin-ASCII; Upper()', $s)
            : strtoupper($s);
        return trim(preg_replace('/\s+/', ' ', (string) $t));
    }

    /** Converte ISO-8859-1 (formato da Receita) para UTF-8. */
    private function utf8(string $s): string
    {
        return mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
    }

    /** Aceita texto que já esteja em UTF-8 (opções digitadas pelo usuário). */
    private function utf8Safe(string $s): string
    {
        return mb_check_encoding($s, 'UTF-8') ? $s : $this->utf8($s);
    }

    /** @return array{0: list<string>, 1: ?string} */
    private function findFiles(string $dir): array
    {
        $estab = [];
        $mun   = null;
        foreach (scandir($dir) ?: [] as $name) {
            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if (! is_file($path)) {
                continue;
            }
            if (preg_match('/^estabelecimentos?\d*\.zip$/i', $name) || preg_match('/ESTABELE$/i', $name)) {
                $estab[] = $path;
            } elseif (preg_match('/^municipios?\.zip$/i', $name) || preg_match('/MUNICCSV$/i', $name)) {
                $mun = $path;
            }
        }
        sort($estab);
        return [$estab, $mun];
    }

    /**
     * Lê linha a linha um .zip (todas as entradas) ou arquivo texto, sem carregar tudo em memória.
     * O callback devolve false para interromper.
     */
    private function eachLine(string $path, callable $cb): void
    {
        if (preg_match('/\.zip$/i', $path)) {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                throw new \RuntimeException("Não consegui abrir {$path}");
                return;
            }
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (str_ends_with($name, '/')) {
                    continue;
                }
                $fh = $zip->getStream($name);
                if (! $fh) {
                    continue;
                }
                $stop = $this->readHandle($fh, $cb);
                fclose($fh);
                if ($stop) {
                    break;
                }
            }
            $zip->close();
            return;
        }

        $fh = fopen($path, 'rb');
        if ($fh) {
            $this->readHandle($fh, $cb);
            fclose($fh);
        }
    }

    /** @return bool true se o callback pediu para parar */
    private function readHandle($fh, callable $cb): bool
    {
        while (($line = fgets($fh)) !== false) {
            if ($cb(rtrim($line, "\r\n")) === false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Escolhe o melhor telefone. Celular (9 dígitos começando com 9; números antigos de 8 dígitos iniciados em 6-9 ganham o 9) tem prioridade.
     * Retorna formatado "(11) 91234-5678" ou null.
     *
     * @param list<array{0:string,1:string}> $pairs [ddd, número]
     */
    private function pickPhone(array $pairs, bool $onlyMobile, ?bool &$isMobile = null): ?string
    {
        $valid = [];
        foreach ($pairs as [$ddd, $num]) {
            $ddd = preg_replace('/\D/', '', (string) $ddd);
            $num = preg_replace('/\D/', '', (string) $num);
            // Celulares antigos vêm cadastrados com 8 dígitos (6-9 no início) e sem o 9 de 2012-2016: normaliza.
            if (strlen($num) === 8 && in_array($num[0], ['6', '7', '8', '9'], true)) {
                $num = '9' . $num;
            }
            if (strlen($ddd) === 2 && $ddd[0] !== '0' && (strlen($num) === 8 || strlen($num) === 9)) {
                $valid[] = [$ddd, $num];
            }
        }
        $mobile = array_values(array_filter($valid, fn($p) => strlen($p[1]) === 9 && $p[1][0] === '9'));
        $pick   = $mobile[0] ?? ($onlyMobile ? null : ($valid[0] ?? null));
        if ($pick === null) {
            return null;
        }
        $isMobile = isset($mobile[0]) && $pick === $mobile[0];
        [$ddd, $num] = $pick;
        return sprintf('(%s) %s-%s', $ddd, substr($num, 0, strlen($num) - 4), substr($num, -4));
    }

    private function hasAnyPhone(array $f): bool
    {
        return trim($f[22] ?? '') !== '' || trim($f[24] ?? '') !== '';
    }

    private function fmtCnpj(string $c): string
    {
        return sprintf('%s.%s.%s/%s-%s', substr($c, 0, 2), substr($c, 2, 3), substr($c, 5, 3), substr($c, 8, 4), substr($c, 12, 2));
    }
}
