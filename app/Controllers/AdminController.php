<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RedirectResponse;

class AdminController extends BaseController
{
    public function approveUser(int $userId): RedirectResponse
    {
        $result = (new UserModel())->approvePendingUser($userId);

        $message = match ($result) {
            UserModel::APPROVAL_SUCCESS => [
                'type' => 'success',
                'text' => 'La solicitud fue aprobada. El usuario ya puede usar las funciones de Propietario.',
            ],
            UserModel::APPROVAL_NOT_PENDING => [
                'type' => 'error',
                'text' => 'La solicitud ya no está pendiente o no existe.',
            ],
            UserModel::APPROVAL_ROLE_MISSING => [
                'type' => 'error',
                'text' => 'No se encontraron los roles necesarios para aprobar la solicitud.',
            ],
            default => [
                'type' => 'error',
                'text' => 'No se pudo aprobar la solicitud. Intentá nuevamente.',
            ],
        };

        session()->setFlashdata('adminMessage', $message);

        return redirect()->to(base_url('home'));
    }
}
