<?php

namespace App\Controllers\Api;

use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use CodeIgniter\Controller;

class Contact extends Controller
{
    public function ticket()
    {
        $name    = trim($this->request->getPost('name')    ?? '');
        $email   = trim($this->request->getPost('email')   ?? '');
        $phone   = trim($this->request->getPost('phone')   ?? '');
        $message = trim($this->request->getPost('message') ?? '');
        $tipo    = $this->request->getPost('tipo') ?? 'geral';

        if ($name === '' || $email === '' || $message === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Nome, e-mail e mensagem são obrigatórios.',
            ]);
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'E-mail inválido.',
            ]);
        }

        $db   = \Config\Database::connect();
        $user = $db->table('users')->where('email', $email)->get()->getRowArray();

        if (! $user) {
            // Cria cliente inativo — admin ativa quando fechar negócio
            $result = $db->query(
                "INSERT INTO users (name, email, phone, password, role, active)
                 VALUES (?, ?, ?, ?, 'client', false) RETURNING id",
                [
                    $name,
                    $email,
                    $phone ?: null,
                    password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT),
                ]
            );
            $clientId = $result->getRow()->id;
        } else {
            $clientId = $user['id'];
            if ($phone && empty($user['phone'])) {
                $db->table('users')->where('id', $clientId)->update(['phone' => $phone]);
            }
        }

        $subjectMap = [
            'vendas'  => "[Orçamento] {$name}",
            'suporte' => "[Suporte] {$name}",
        ];
        $subject = $subjectMap[$tipo] ?? "[Contato] {$name}";

        $ticketId = (new TicketModel())->insert([
            'client_id' => $clientId,
            'subject'   => $subject,
            'status'    => 'open',
            'priority'  => 'normal',
        ]);

        (new TicketMessageModel())->insert([
            'ticket_id' => $ticketId,
            'sender_id' => $clientId,
            'message'   => $message,
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Mensagem enviada com sucesso! Em breve entraremos em contato.',
        ]);
    }
}
