<?php

namespace App\Controllers\Client;

use App\Models\ContractModel;
use CodeIgniter\Controller;

class Contracts extends Controller
{
    public function show(int $id): string
    {
        $clientId = session()->get('user_id');
        $contract = (new ContractModel())
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (! $contract || $contract['status'] === 'draft') {
            return redirect()->to('/app/documents')->with('error', 'Contrato não encontrado.');
        }

        return view('client/contract-view', ['contract' => $contract]);
    }

    public function accept(int $id)
    {
        $clientId = session()->get('user_id');
        $contract = (new ContractModel())
            ->where('client_id', $clientId)
            ->where('id', $id)
            ->first();

        if (! $contract || $contract['status'] !== 'sent') {
            return redirect()->to('/app/documents')->with('error', 'Contrato não disponível para aceite.');
        }

        (new ContractModel())->update($id, [
            'status'      => 'accepted',
            'accepted_at' => date('Y-m-d H:i:s'),
            'accepted_ip' => $this->request->getIPAddress(),
        ]);

        return redirect()->to("/app/contracts/{$id}")->with('success', 'Contrato aceito. Obrigado!');
    }
}
