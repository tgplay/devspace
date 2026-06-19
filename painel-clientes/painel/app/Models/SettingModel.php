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
}
