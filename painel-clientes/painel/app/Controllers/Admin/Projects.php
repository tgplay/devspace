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
