<?php

namespace App\Controllers\Admin;

use App\Models\ProspectModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Prospects extends Controller
{
    private const VALID_STATUSES = ['new', 'contacted', 'qualified', 'proposal_sent', 'won', 'lost'];

    public function index(): string
    {
        $model  = new ProspectModel();
        $filter = $this->request->getGet('status') ?? 'all';

        $query = $model->orderBy('created_at', 'DESC');
        if ($filter !== 'all') {
            $query->where('status', $filter);
        }

        return view('admin/prospects/index', [
            'prospects'    => $query->findAll(),
            'statusCounts' => $model->countByStatus(),
            'activeFilter' => $filter,
        ]);
    }

    public function create(): string
    {
        return view('admin/prospects/show', ['prospect' => null]);
    }

    public function store()
    {
        $data = $this->extractFields();

        if ($data['name'] === '' || $data['email'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nome e e-mail são obrigatórios.');
        }
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'E-mail inválido.');
        }

        $data['status'] = 'new';
        (new ProspectModel())->insert($data);

        return redirect()->to('/admin/prospects')->with('success', 'Prospecto criado com sucesso.');
    }

    public function show(int $id): string
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }

        return view('admin/prospects/show', ['prospect' => $prospect]);
    }

    public function update(int $id)
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }

        $data = $this->extractFields();
        $data['status'] = $this->request->getPost('status') ?? $prospect['status'];

        if ($data['name'] === '' || $data['email'] === '') {
            return redirect()->back()->withInput()->with('error', 'Nome e e-mail são obrigatórios.');
        }
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'E-mail inválido.');
        }

        (new ProspectModel())->update($id, $data);

        return redirect()->to("/admin/prospects/{$id}")->with('success', 'Prospecto atualizado.');
    }

    public function updateStatus(int $id)
    {
        $status = $this->request->getPost('status');

        if (! in_array($status, self::VALID_STATUSES, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Status inválido.']);
        }

        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return $this->response->setJSON(['success' => false, 'message' => 'Prospecto não encontrado.']);
        }

        (new ProspectModel())->update($id, ['status' => $status]);

        return $this->response->setJSON(['success' => true, 'status' => $status]);
    }

    public function delete(int $id)
    {
        (new ProspectModel())->delete($id);
        return redirect()->to('/admin/prospects')->with('success', 'Prospecto removido.');
    }

    public function convertToClient(int $id)
    {
        $prospect = (new ProspectModel())->find($id);
        if (! $prospect) {
            return redirect()->to('/admin/prospects')->with('error', 'Prospecto não encontrado.');
        }

        $userModel = new UserModel();
        if ($userModel->where('email', $prospect['email'])->first()) {
            return redirect()->back()->with('error', 'Já existe um cliente cadastrado com este e-mail.');
        }

        $tempPassword = substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789'), 0, 10);

        $clientId = $userModel->insert([
            'name'     => $prospect['name'],
            'email'    => $prospect['email'],
            'phone'    => $prospect['phone'],
            'password' => password_hash($tempPassword, PASSWORD_DEFAULT),
            'role'     => 'client',
            'active'   => true,
        ]);

        (new ProspectModel())->update($id, [
            'status' => 'won',
            'notes'  => trim(($prospect['notes'] ?? '') . "\n\n[Convertido em cliente #$clientId]"),
        ]);

        return redirect()->to("/admin/clients/{$clientId}")
            ->with('success', "Cliente criado com sucesso! Senha temporária: {$tempPassword}")
            ->with('temp_password', $tempPassword);
    }

    private function extractFields(): array
    {
        return [
            'name'     => trim($this->request->getPost('name')    ?? ''),
            'email'    => trim($this->request->getPost('email')   ?? ''),
            'phone'    => trim($this->request->getPost('phone')   ?? '') ?: null,
            'company'  => trim($this->request->getPost('company') ?? '') ?: null,
            'interest' => $this->request->getPost('interest') ?? 'other',
            'source'   => $this->request->getPost('source')   ?? 'other',
            'notes'    => trim($this->request->getPost('notes')   ?? '') ?: null,
        ];
    }
}
