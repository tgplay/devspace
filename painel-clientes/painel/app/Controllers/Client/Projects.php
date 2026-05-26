<?php

namespace App\Controllers\Client;

use App\Models\ProjectModel;
use App\Models\TaskModel;
use CodeIgniter\Controller;

class Projects extends Controller
{
    public function index()
    {
        return view('client/projects/index', [
            'projects' => (new ProjectModel())->where('client_id', session()->get('user_id'))->findAll(),
        ]);
    }

    public function show(int $id)
    {
        $project = (new ProjectModel())->where('client_id', session()->get('user_id'))->find($id);
        if (! $project) {
            return redirect()->to('/app/projects')->with('error', 'Projeto não encontrado.');
        }

        return view('client/projects/show', [
            'project' => $project,
            'tasks'   => (new TaskModel())->where('project_id', $id)->orderBy('sort_order')->findAll(),
        ]);
    }
}
