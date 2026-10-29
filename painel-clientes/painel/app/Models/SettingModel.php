<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'key';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $allowedFields    = ['key', 'value'];
    protected $useTimestamps    = false;

    public function get(string $key, $default = null)
    {
        $row = $this->find($key);
        return $row ? $row['value'] : $default;
    }

    public function put(string $key, $value): void
    {
        if ($this->find($key)) {
            $this->update($key, ['value' => $value]);
        } else {
            $this->insert(['key' => $key, 'value' => $value]);
        }
    }

    /**
     * Segredos (chaves de API): a variável de ambiente tem prioridade sobre o valor
     * salvo na tabela settings. Em produção, prefira sempre a variável de ambiente.
     */
    public function secret(string $key, string $envName): string
    {
        $fromEnv = (string) env($envName, '');
        return $fromEnv !== '' ? $fromEnv : (string) $this->get($key, '');
    }

    /** Dica mascarada para exibir na tela sem expor a chave (ex.: "••••a1b2"). */
    public static function mask(string $secret): string
    {
        return $secret === '' ? '' : '••••' . substr($secret, -4);
    }
}
