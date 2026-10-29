<?php

namespace App\Controllers\Admin;

use App\Models\SettingModel;
use CodeIgniter\Controller;

class AiSettings extends Controller
{
    private SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    public function index(): string
    {
        return view('admin/ai-settings/index', [
            'title'  => 'Configurações de IA',
            'apiKey'     => $this->settings->secret('groq_api_key', 'GROQ_API_KEY'),
            'apiKeyHint' => SettingModel::mask($this->settings->secret('groq_api_key', 'GROQ_API_KEY')),
            'fromEnv'    => (string) env('GROQ_API_KEY', '') !== '',
            'waLimit'    => (int) $this->settings->get('whatsapp_daily_limit', 20),
        ]);
    }

    public function save()
    {
        $limit = $this->request->getPost('whatsapp_daily_limit');
        if ($limit !== null && $limit !== '' && ctype_digit((string) $limit)) {
            $this->settings->put('whatsapp_daily_limit', (string) min(200, (int) $limit));
        }

        $key = trim((string) $this->request->getPost('api_key'));
        if ($key !== '') { // campo vazio = manter a chave atual
            $this->settings->put('groq_api_key', $key);
        }

        return redirect()->to('/admin/ai-settings')->with('success', 'Configurações salvas.');
    }
}
