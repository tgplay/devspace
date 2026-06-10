<?php

namespace App\Controllers\Admin;

use App\Models\ContractTemplateModel;
use CodeIgniter\Controller;

class ContractTemplates extends Controller
{
    public function index(): string
    {
        return view('admin/contract-templates/index', [
            'templates' => (new ContractTemplateModel())->orderBy('name')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('admin/contract-templates/show', ['template' => null]);
    }

    public function store()
    {
        $data = $this->extractFields();
        if ($data['name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nome é obrigatório.');
        }
        (new ContractTemplateModel())->insert($data);
        return redirect()->to('/admin/contract-templates')->with('success', 'Modelo criado.');
    }

    public function show(int $id): string
    {
        $template = (new ContractTemplateModel())->find($id);
        if (! $template) {
            return redirect()->to('/admin/contract-templates')->with('error', 'Modelo não encontrado.');
        }
        return view('admin/contract-templates/show', ['template' => $template]);
    }

    public function update(int $id)
    {
        $template = (new ContractTemplateModel())->find($id);
        if (! $template) {
            return redirect()->to('/admin/contract-templates')->with('error', 'Modelo não encontrado.');
        }
        $data = $this->extractFields();
        if ($data['name'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nome é obrigatório.');
        }
        (new ContractTemplateModel())->update($id, $data);
        return redirect()->to("/admin/contract-templates/{$id}")->with('success', 'Modelo atualizado.');
    }

    public function delete(int $id)
    {
        (new ContractTemplateModel())->delete($id);
        return redirect()->to('/admin/contract-templates')->with('success', 'Modelo removido.');
    }

    public function content(int $id)
    {
        $template = (new ContractTemplateModel())->find($id);
        return $this->response->setJSON([
            'content' => $template ? $template['content'] : '',
        ]);
    }

    private function extractFields(): array
    {
        return [
            'name'    => trim($this->request->getPost('name') ?? ''),
            'content' => $this->request->getPost('content') ?? '',
        ];
    }
}
