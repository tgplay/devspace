<?php

namespace App\Controllers\Client;

use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use CodeIgniter\Controller;

class Support extends Controller
{
    public function index()
    {
        return view('client/support/index', [
            'tickets' => (new TicketModel())->where('client_id', session()->get('user_id'))->orderBy('created_at', 'DESC')->findAll(),
        ]);
    }

    public function newTicket()
    {
        return view('client/support/new');
    }

    public function store()
    {
        $subject = trim($this->request->getPost('subject'));
        $message = trim($this->request->getPost('message'));

        if (! $subject || ! $message) {
            return redirect()->back()->with('error', 'Preencha o assunto e a mensagem.');
        }

        $ticketId = (new TicketModel())->insert([
            'client_id' => session()->get('user_id'),
            'subject'   => $subject,
        ]);

        (new TicketMessageModel())->insert([
            'ticket_id' => $ticketId,
            'sender_id' => session()->get('user_id'),
            'message'   => $message,
        ]);

        return redirect()->to('/app/support')->with('success', 'Chamado aberto com sucesso.');
    }

    public function show(int $id)
    {
        $ticket = (new TicketModel())->where('client_id', session()->get('user_id'))->find($id);
        if (! $ticket) {
            return redirect()->to('/app/support')->with('error', 'Chamado não encontrado.');
        }

        return view('client/support/show', [
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

        return redirect()->to("/app/support/{$id}")->with('success', 'Mensagem enviada.');
    }
}
