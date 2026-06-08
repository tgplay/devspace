<?php

namespace App\Controllers\Admin;

use App\Models\ProjectModel;
use App\Models\TaskModel;
use App\Models\UserModel;
use CodeIgniter\Controller;

class Projects extends Controller
{
    public function index()
    {
        return view('admin/projects/index', [
            'projects' => (new ProjectModel())->findAllWithClient(),
        ]);
    }

    public function create()
    {
        return view('admin/projects/create', [
            'clients' => (new UserModel())->where('role', 'client')->where('active', true)->orderBy('name')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'client_id'   => 'required|is_natural_no_zero',
            'name'        => 'required|min_length[2]|max_length[200]',
            'type'        => 'required|in_list[site,app,system,other]',
            'description' => 'permit_empty|max_length[2000]',
            'deadline'    => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = (new ProjectModel())->insert([
            'client_id'   => $this->request->getPost('client_id'),
            'name'        => $this->request->getPost('name'),
            'type'        => $this->request->getPost('type'),
            'description' => $this->request->getPost('description'),
            'deadline'    => $this->request->getPost('deadline') ?: null,
            'status'      => 'planning',
            'progress'    => 0,
        ]);

        return redirect()->to("/admin/projects/{$id}")->with('success', 'Projeto criado com sucesso.');
    }

    public function show(int $id)
    {
        $project = (new ProjectModel())->find($id);
        if (! $project) {
            return redirect()->to('/admin/projects')->with('error', 'Projeto não encontrado.');
        }

        return view('admin/projects/show', [
            'project' => $project,
            'client'  => (new UserModel())->find($project['client_id']),
            'tasks'   => (new TaskModel())->where('project_id', $id)->orderBy('sort_order')->findAll(),
        ]);
    }

    public function rename(int $id)
    {
        $project = (new ProjectModel())->find($id);
        if (! $project) {
            return $this->response->setJSON(['success' => false, 'message' => 'Projeto não encontrado.']);
        }

        $name = trim($this->request->getPost('name') ?? '');
        if (strlen($name) < 2) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nome deve ter no mínimo 2 caracteres.']);
        }

        (new ProjectModel())->where('id', $id)->set(['name' => $name])->update();

        return $this->response->setJSON(['success' => true, 'message' => 'Nome atualizado.', 'name' => $name]);
    }

    public function update(int $id)
    {
        $model = new ProjectModel();
        $model->update($id, [
            'status'       => $this->request->getPost('status'),
            'progress'     => (int) $this->request->getPost('progress'),
            'delivery_url' => $this->request->getPost('delivery_url'),
            'deadline'     => $this->request->getPost('deadline') ?: null,
        ]);

        return redirect()->to("/admin/projects/{$id}")->with('success', 'Projeto atualizado.');
    }
}
