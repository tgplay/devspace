<?php

namespace App\Controllers\Admin;

use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use CodeIgniter\Controller;

class Support extends Controller
{
    public function index()
    {
        return view('admin/support/index', [
            'tickets' => (new TicketModel())->findAllWithClient(),
        ]);
    }

    public function show(int $id)
    {
        $ticket = (new TicketModel())->find($id);
        if (! $ticket) {
            return redirect()->to('/admin/support')->with('error', 'Chamado não encontrado.');
        }

        return view('admin/support/show', [
            'ticket'   => $ticket,
            'messages' => (new TicketMessageModel())->where('ticket_id', $id)->orderBy('created_at')->findAll(),
        ]);
    }

    public function reply(int $id)
    {
        $msg = trim($this->request->getPost('message'));
        if (! $msg) {
            return redirect()->back()->with('error', 'Mensagem não pode ser vazia.');
        }

        (new TicketMessageModel())->insert([
            'ticket_id' => $id,
            'sender_id' => session()->get('user_id'),
            'message'   => $msg,
        ]);

        (new TicketModel())->update($id, ['status' => 'attending']);

        return redirect()->to("/admin/support/{$id}")->with('success', 'Resposta enviada.');
    }
}
