<?php

namespace App\Controllers\Admin;

use App\Libraries\CnpjImporter;
use App\Models\SettingModel;
use CodeIgniter\Controller;

/** Tela "Captação CNPJ": importa leads da base aberta de CNPJ da Receita Federal (fonte gratuita). */
class CnpjImport extends Controller
{
    private SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    private function dir(): string
    {
        return WRITEPATH . 'cnpj';
    }

    private function lockPath(): string
    {
        return $this->dir() . DIRECTORY_SEPARATOR . '.import.lock';
    }

    private function stopPath(): string
    {
        return $this->dir() . DIRECTORY_SEPARATOR . '.import.stop';
    }

    /** @return array{running:bool,pid:int,started:int,mode:string} */
    private function runState(): array
    {
        $state = ['running' => false, 'pid' => 0, 'started' => 0, 'mode' => ''];
        if (! is_file($this->lockPath())) {
            return $state;
        }
        $fh = @fopen($this->lockPath(), 'r');
        if (! $fh) {
            return $state;
        }
        if (flock($fh, LOCK_SH | LOCK_NB)) {
            flock($fh, LOCK_UN); // ninguém segura o lock: não há importação rodando
        } else {
            $state['running'] = true;
            $meta = json_decode((string) stream_get_contents($fh), true);
            if (is_array($meta)) {
                $state['pid']     = (int) ($meta['pid'] ?? 0);
                $state['started'] = (int) ($meta['started'] ?? 0);
                $state['mode']    = (string) ($meta['mode'] ?? '');
            }
        }
        fclose($fh);

        return $state;
    }

    /** GET: estado da importação (a tela usa para mostrar o botão Parar mesmo após recarregar). */
    public function status()
    {
        return $this->response->setJSON($this->runState());
    }

    /**
     * POST: pede para a importação em andamento parar.
     * Padrão: sinaliza (parada limpa, em segundos). force=1: encerra o processo PHP que a executa (travou).
     */
    public function stop()
    {
        $state = $this->runState();
        if (! $state['running']) {
            @unlink($this->stopPath());
            return $this->response->setJSON(['ok' => true, 'message' => 'Nenhuma importação em andamento.']);
        }

        if ($this->request->getPost('force') === '1') {
            if ($state['pid'] > 1 && function_exists('posix_kill') && $state['pid'] !== getmypid()) {
                posix_kill($state['pid'], 9); // SIGKILL; o SO libera o flock
                usleep(300000);
                @unlink($this->stopPath());
                $after = $this->runState();
                return $this->response->setJSON([
                    'ok'      => ! $after['running'],
                    'message' => $after['running']
                        ? 'Não consegui encerrar o processo. Reinicie o container php (docker compose restart php).'
                        : 'Processo encerrado à força. O que já foi gravado permanece.',
                ]);
            }
            return $this->response->setJSON(['ok' => false, 'message' => 'Não foi possível identificar o processo. Reinicie o container php (docker compose restart php).']);
        }

        @file_put_contents($this->stopPath(), (string) time());
        return $this->response->setJSON(['ok' => true, 'message' => 'Parada solicitada. Deve encerrar em alguns segundos.']);
    }

    public function index(): string
    {
        $scan  = CnpjImporter::scanDir($this->dir());
        $files = [];
        foreach (array_merge($scan['estab'], $scan['mun'] ? [$scan['mun']] : []) as $path) {
            $files[] = ['name' => basename($path), 'size' => filesize($path) ?: 0, 'mtime' => filemtime($path) ?: 0];
        }

        return view('admin/cnpj-import/index', [
            'title'      => 'Captação CNPJ',
            'files'      => $files,
            'filesOk'    => count($scan['estab']) > 0 && $scan['mun'] !== null,
            'estabCount' => count($scan['estab']),
            'cfg'        => $this->config(),
        ]);
    }

    public function save()
    {
        $this->saveConfig($this->readInput());
        return redirect()->to('/admin/cnpj-import')->with('success', 'Configurações salvas.');
    }

    /** POST: roda simulação (mode=dry) ou importação (mode=real) com log em tempo real (SSE sobre fetch). */
    public function run(): void
    {
        $in   = $this->readInput();
        $real = $this->request->getPost('mode') === 'real';
        $this->saveConfig($in);

        // Libera o lock da sessão: a execução é longa e travaria outras páginas do admin.
        session_write_close();

        @ob_end_clean();
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        set_time_limit(0);
        ignore_user_abort($real); // importação real termina mesmo se a aba for fechada; simulação é cancelável

        $send = function (string $type, array $data): void {
            echo 'data: ' . json_encode(['type' => $type, 'data' => $data], JSON_UNESCAPED_UNICODE) . "\n\n";
            @ob_flush();
            flush();
        };

        if (! $in['municipio'] || ! $in['cnae']) {
            $send('error', ['message' => 'Informe ao menos um município e um CNAE.']);
            exit;
        }

        // Uma importação por vez
        if (! is_dir($this->dir())) {
            @mkdir($this->dir(), 0775, true);
        }
        $lock = @fopen($this->lockPath(), 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            $send('error', ['message' => 'Já existe uma importação em andamento. Use "Parar importação" para encerrá-la.', 'busy' => true]);
            exit;
        }
        ftruncate($lock, 0);
        fwrite($lock, json_encode(['pid' => getmypid(), 'started' => time(), 'mode' => $real ? 'real' : 'dry']));
        fflush($lock);
        @unlink($this->stopPath()); // descarta pedido de parada antigo
        $stopPath = $this->stopPath();

        try {
            $importer = new CnpjImporter([
                'municipio'   => CnpjImporter::csv($in['municipio']),
                'cnae'        => CnpjImporter::csv($in['cnae']),
                'bairro'      => CnpjImporter::csv($in['bairro']),
                'dir'         => $this->dir(),
                'competencia' => $in['competencia'] !== '' ? $in['competencia'] : date('Y-m'),
                'limit'       => $in['limit'],
                'min_meses'   => $in['min_meses'],
                'so_celular'  => $in['so_celular'],
                'dry_run'     => ! $real,
                'should_stop' => static function () use ($stopPath): bool {
                    clearstatcache(true, $stopPath);
                    return is_file($stopPath);
                },
            ], function (string $type, array $d) use ($send) {
                $send($type === 'sample' ? 'lead' : $type, $d);
            });

            $result = $importer->run();
            $send('done', $result);
        } catch (\RuntimeException $e) {
            $send('error', ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            log_message('error', 'CnpjImport: ' . $e->getMessage());
            $send('error', ['message' => 'Erro inesperado durante a importação. Veja os logs do servidor.']);
        } finally {
            @unlink($stopPath);
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        exit;
    }

    // ── config ────────────────────────────────────────────────────────────────

    private function config(): array
    {
        return [
            'municipio'   => $this->settings->get('cnpj_municipio', 'SAO PAULO'),
            'cnae'        => $this->settings->get('cnpj_cnae', '5611,1091'),
            'bairro'      => $this->settings->get('cnpj_bairro', ''),
            'limit'       => (int) $this->settings->get('cnpj_limit', 300),
            'min_meses'   => (int) $this->settings->get('cnpj_min_meses', 6),
            'so_celular'  => $this->settings->get('cnpj_so_celular', '0') === '1',
            'competencia' => $this->settings->get('cnpj_competencia', ''),
        ];
    }

    private function readInput(): array
    {
        $s = fn(string $k, int $max = 300) => mb_substr(trim((string) $this->request->getPost($k)), 0, $max);

        return [
            'municipio'   => $s('municipio'),
            'cnae'        => $s('cnae'),
            'bairro'      => $s('bairro', 600),
            'limit'       => max(1, min(5000, (int) ($this->request->getPost('limit') ?: 300))),
            'min_meses'   => max(0, min(600, (int) ($this->request->getPost('min_meses') ?? 6))),
            'so_celular'  => $this->request->getPost('so_celular') === '1',
            'competencia' => preg_match('/^\d{4}-\d{2}$/', $s('competencia', 7)) ? $s('competencia', 7) : '',
        ];
    }

    private function saveConfig(array $in): void
    {
        $this->settings->put('cnpj_municipio',   $in['municipio']);
        $this->settings->put('cnpj_cnae',        $in['cnae']);
        $this->settings->put('cnpj_bairro',      $in['bairro']);
        $this->settings->put('cnpj_limit',       (string) $in['limit']);
        $this->settings->put('cnpj_min_meses',   (string) $in['min_meses']);
        $this->settings->put('cnpj_so_celular',  $in['so_celular'] ? '1' : '0');
        $this->settings->put('cnpj_competencia', $in['competencia']);
    }
}
