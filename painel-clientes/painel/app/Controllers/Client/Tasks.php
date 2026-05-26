<?php

namespace App\Controllers\Client;

use App\Models\NotificationModel;
use App\Models\TaskModel;
use CodeIgniter\Controller;

class Tasks extends Controller
{
    public function approve(int $taskId)
    {
        $task = (new TaskModel())->find($taskId);
        if (! $task || $task['status'] !== 'awaiting_approval') {
            return redirect()->back()->with('error', 'Tarefa inválida para aprovação.');
        }

        (new TaskModel())->update($taskId, [
            'status'      => 'done',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => session()->get('user_id'),
        ]);

        (new NotificationModel())->insert([
            'user_id' => session()->get('user_id'),
            'title'   => 'Entrega aprovada',
            'message' => "Você aprovou a etapa: {$task['title']}",
        ]);

        return redirect()->back()->with('success', 'Etapa aprovada com sucesso.');
    }

    public function requestRevision(int $taskId)
    {
        (new TaskModel())->update($taskId, ['status' => 'revision_requested']);
        return redirect()->back()->with('success', 'Solicitação de revisão enviada.');
    }
}
