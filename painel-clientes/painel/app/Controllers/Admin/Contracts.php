<?php

namespace App\Controllers\Admin;

use App\Models\ContractModel;
use App\Models\ContractTemplateModel;
use App\Models\ProjectModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Contracts extends Controller
{
    private const VALID_STATUSES = ['draft', 'sent', 'accepted', 'closed'];

    public function index(): string
    {
        return view('admin/contracts/index', [
            'contracts' => (new ContractModel())
                ->withClient()
                ->orderBy('contracts.created_at', 'DESC')
                ->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('admin/contracts/show', $this->viewData(null));
    }

    public function store()
    {
        $data = $this->extractFields();
        if ($data['title'] === '' || ! $data['client_id']) {
            return redirect()->back()->withInput()->with('error', 'Título e cliente são obrigatórios.');
        }
        $id = (new ContractModel())->insert($data);
        return redirect()->to("/admin/contracts/{$id}")->with('success', 'Contrato criado.');
    }

    public function show(int $id): string
    {
        $contract = (new ContractModel())->find($id);
        if (! $contract) {
            return redirect()->to('/admin/contracts')->with('error', 'Contrato não encontrado.');
        }
        return view('admin/contracts/show', $this->viewData($contract));
    }

    public function update(int $id)
    {
        $contract = (new ContractModel())->find($id);
        if (! $contract) {
            return redirect()->to('/admin/contracts')->with('error', 'Contrato não encontrado.');
        }
        $data = $this->extractFields();
        if ($data['title'] === '') {
            return redirect()->back()->withInput()->with('error', 'Título é obrigatório.');
        }
        (new ContractModel())->update($id, $data);
        return redirect()->to("/admin/contracts/{$id}")->with('success', 'Contrato atualizado.');
    }

    public function send(int $id)
    {
        $contract = (new ContractModel())->find($id);
        if (! $contract) {
            return redirect()->to('/admin/contracts')->with('error', 'Contrato não encontrado.');
        }
        if ($contract['status'] === 'draft') {
            (new ContractModel())->update($id, ['status' => 'sent']);
        }
        return redirect()->to("/admin/contracts/{$id}")->with('success', 'Contrato enviado ao cliente.');
    }

    public function delete(int $id)
    {
        (new ContractModel())->delete($id);
        return redirect()->to('/admin/contracts')->with('success', 'Contrato removido.');
    }

    private function viewData(?array $contract): array
    {
        return [
            'contract'  => $contract,
            'clients'   => (new UserModel())->where('role', 'client')->where('active', true)->orderBy('name')->findAll(),
            'projects'  => (new ProjectModel())->orderBy('name')->findAll(),
            'templates' => (new ContractTemplateModel())->orderBy('name')->findAll(),
        ];
    }

    private function extractFields(): array
    {
        $value = $this->request->getPost('value');
        return [
            'client_id'   => (int) ($this->request->getPost('client_id') ?? 0) ?: null,
            'project_id'  => (int) ($this->request->getPost('project_id') ?? 0) ?: null,
            'template_id' => (int) ($this->request->getPost('template_id') ?? 0) ?: null,
            'title'       => trim($this->request->getPost('title') ?? ''),
            'content'     => $this->request->getPost('content') ?? '',
            'value'       => ($value !== null && $value !== '') ? (float) $value : null,
            'start_date'  => $this->request->getPost('start_date') ?: null,
            'end_date'    => $this->request->getPost('end_date') ?: null,
            'status'      => in_array($this->request->getPost('status'), self::VALID_STATUSES, true)
                              ? $this->request->getPost('status') : 'draft',
        ];
    }
}
