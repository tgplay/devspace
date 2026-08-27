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
            'apiKey' => $this->settings->get('groq_api_key', ''),
        ]);
    }

    public function save()
    {
        $this->settings->put('groq_api_key', trim($this->request->getPost('api_key')));

        return redirect()->to('/admin/ai-settings')->with('success', 'Configurações salvas.');
    }
}
