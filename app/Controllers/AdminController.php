<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\RolModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

class AdminController extends BaseController
{
    private function currentAdminId(): int
    {
        return (int) session()->get('user_id');
    }

    public function users(): string
    {
        $users = new UserModel();

        return view('usuarios/index.php', [
            'user'           => [
                'id'     => session()->get('user_id'),
                'email'  => session()->get('user_email'),
                'nombre' => session()->get('user_name'),
                'role'   => session()->get('user_role'),
            ],
            'pendingUsers'   => $users->findPendingApprovals(),
            'allUsers'       => $users->findAllUserSummaries(),
            'adminMessage'   => session()->getFlashdata('adminMessage'),
            'currentAdminId' => $this->currentAdminId(),
        ]);
    }

    public function approveUser(int $userId): RedirectResponse
    {
        $requestedRole = $this->request->getPost('role');
        $targetRole = is_string($requestedRole) ? $requestedRole : '';
        $result = (new UserModel())->approvePendingUser($userId, $targetRole);

        $message = match ($result) {
            UserModel::APPROVAL_SUCCESS => [
                'type' => 'success',
                'text' => 'La solicitud fue aprobada. El usuario ya puede usar las funciones de ' . $targetRole . '.',
            ],
            UserModel::APPROVAL_NOT_PENDING => [
                'type' => 'error',
                'text' => 'La solicitud ya no está pendiente o no existe.',
            ],
            UserModel::APPROVAL_INVALID_ROLE => [
                'type' => 'error',
                'text' => 'Elegí un rol válido: Propietario o Administrador.',
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

        return redirect()->to(site_url('usuarios'));
    }

    public function rejectUser(int $userId): RedirectResponse
    {
        $result = (new UserModel())->rejectPendingUser($userId);
        $message = match ($result) {
            UserModel::REQUEST_REJECTION_SUCCESS => [
                'type' => 'success',
                'text' => 'La solicitud fue rechazada. La cuenta y sus datos quedaron conservados.',
            ],
            UserModel::REQUEST_REJECTION_NOT_PENDING => [
                'type' => 'error',
                'text' => 'La solicitud ya no está pendiente o no existe.',
            ],
            default => [
                'type' => 'error',
                'text' => 'No se pudo rechazar la solicitud. Intentá nuevamente.',
            ],
        };

        session()->setFlashdata('adminMessage', $message);

        return redirect()->to(site_url('usuarios'));
    }

    public function reopenUserRequest(int $userId): RedirectResponse
    {
        $result = (new UserModel())->reopenRejectedRequest($userId);
        $message = match ($result) {
            UserModel::REQUEST_REOPEN_SUCCESS => [
                'type' => 'success',
                'text' => 'La solicitud volvió a quedar pendiente de revisión.',
            ],
            UserModel::REQUEST_REOPEN_NOT_REJECTED => [
                'type' => 'error',
                'text' => 'La solicitud no está rechazada o la cuenta no está activa.',
            ],
            default => [
                'type' => 'error',
                'text' => 'No se pudo reabrir la solicitud. Intentá nuevamente.',
            ],
        };

        session()->setFlashdata('adminMessage', $message);

        return redirect()->to(site_url('usuarios'));
    }

    public function editUser(int $userId): string|ResponseInterface
    {
        $users = new UserModel();
        $user = $users->findManagedUserById($userId);
        if ($user === null) {
            return $this->response->setStatusCode(404)->setBody('No se encontró el usuario solicitado.');
        }

        return view('usuarios/edit.php', [
            'user' => $user,
            'admin' => [
                'nombre' => session()->get('user_name'),
                'role'   => session()->get('user_role'),
            ],
            'currentAdminId' => $this->currentAdminId(),
            'errors' => session()->getFlashdata('userEditErrors') ?? [],
            'formData' => session()->getFlashdata('userEditFormData') ?? [],
            'adminMessage' => session()->getFlashdata('adminMessage'),
        ]);
    }

    public function updateUser(int $userId): RedirectResponse
    {
        $users = new UserModel();
        $user = $users->findManagedUserById($userId);
        if ($user === null) {
            session()->setFlashdata('adminMessage', [
                'type' => 'error',
                'text' => 'No se encontró el usuario que intentás modificar.',
            ]);

            return redirect()->to(site_url('usuarios'));
        }

        $submittedData = [];
        foreach (['nombre', 'apellido', 'email', 'telefono'] as $field) {
            $value = $this->request->getPost($field);
            $submittedData[$field] = is_scalar($value) ? trim((string) $value) : '';
        }

        $canChangeRole = $this->canChangeRole($user, $userId);
        $roleValue = $this->request->getPost('role');
        $targetRole = $canChangeRole && is_string($roleValue) ? $roleValue : null;
        $rules = [
            'nombre'   => 'required|max_length[100]',
            'apellido' => 'required|max_length[100]',
            'email'    => 'required|valid_email|max_length[150]|is_unique[usuarios.email,id,' . $userId . ']',
            'telefono' => 'permit_empty|max_length[30]|regex_match[/^[0-9+() -]{8,30}$/]',
        ];
        if ($canChangeRole) {
            $rules['role'] = 'required|in_list[Propietario,Administrador]';
            $submittedData['role'] = is_string($roleValue) ? $roleValue : '';
        }

        if (! $this->validateData($submittedData, $rules)) {
            return redirect()->to(site_url('usuarios/' . $userId . '/editar'))
                ->with('userEditErrors', $this->validator->getErrors())
                ->with('userEditFormData', $submittedData);
        }

        $result = $users->updateManagedUser(
            $userId,
            array_intersect_key($submittedData, array_flip(['nombre', 'apellido', 'email', 'telefono'])),
            $targetRole,
            $this->currentAdminId()
        );

        $message = match ($result) {
            UserModel::USER_UPDATE_SUCCESS => [
                'type' => 'success',
                'text' => 'Los datos del usuario se actualizaron correctamente.',
            ],
            UserModel::USER_UPDATE_INVALID_ROLE => [
                'type' => 'error',
                'text' => 'El rol seleccionado no es válido.',
            ],
            UserModel::USER_UPDATE_INVALID_STATE => [
                'type' => 'error',
                'text' => 'El rol solo se puede modificar en cuentas activas con solicitud aprobada.',
            ],
            UserModel::USER_UPDATE_SELF_ROLE_CHANGE => [
                'type' => 'error',
                'text' => 'No podés cambiar tu propio rol.',
            ],
            UserModel::USER_UPDATE_LAST_ADMIN => [
                'type' => 'error',
                'text' => 'No se puede quitar el rol del último administrador activo.',
            ],
            UserModel::USER_UPDATE_NOT_FOUND => [
                'type' => 'error',
                'text' => 'No se encontró el usuario que intentás modificar.',
            ],
            default => [
                'type' => 'error',
                'text' => 'No se pudieron guardar los cambios. Revisá los datos e intentá nuevamente.',
            ],
        };

        session()->setFlashdata('adminMessage', $message);

        return redirect()->to(site_url('usuarios'));
    }

    public function deactivateUser(int $userId): RedirectResponse
    {
        $result = (new UserModel())->deactivateUser($userId, $this->currentAdminId());
        $message = match ($result) {
            UserModel::USER_DEACTIVATION_SUCCESS => [
                'type' => 'success',
                'text' => 'La cuenta se dio de baja. Se conservaron sus inmuebles y su historial.',
            ],
            UserModel::USER_DEACTIVATION_SELF => [
                'type' => 'error',
                'text' => 'No podés dar de baja tu propia cuenta.',
            ],
            UserModel::USER_DEACTIVATION_LAST_ADMIN => [
                'type' => 'error',
                'text' => 'No se puede dar de baja al último administrador activo.',
            ],
            UserModel::USER_DEACTIVATION_NOT_FOUND => [
                'type' => 'error',
                'text' => 'No se encontró una cuenta activa para dar de baja.',
            ],
            default => [
                'type' => 'error',
                'text' => 'No se pudo dar de baja la cuenta. Intentá nuevamente.',
            ],
        };

        session()->setFlashdata('adminMessage', $message);

        return redirect()->to(site_url('usuarios'));
    }

    public function userProperties(int $userId): string|ResponseInterface
    {
        $users = new UserModel();
        $user = $users->findManagedUserById($userId);
        if ($user === null) {
            return $this->response->setStatusCode(404)->setBody('No se encontró el usuario solicitado.');
        }

        return view('usuarios/inmuebles.php', [
            'user' => $user,
            'admin' => [
                'nombre' => session()->get('user_name'),
                'role'   => session()->get('user_role'),
            ],
            'properties' => $users->findPropertiesForUser($userId),
        ]);
    }

    /**
     * @param array<string, mixed> $user
     */
    private function canChangeRole(array $user, int $userId): bool
    {
        return $user['activo']
            && $user['estado_solicitud'] === UserModel::REQUEST_APPROVED
            && $userId !== $this->currentAdminId()
            && in_array($user['role'], [RolModel::ROLE_OWNER, RolModel::ROLE_ADMINISTRATOR], true);
    }
}
