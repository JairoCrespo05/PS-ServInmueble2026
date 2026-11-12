<?php

namespace App\Filters;

use App\Models\UserModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $userId = filter_var(session()->get('user_id'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($userId === false) {
            session()->destroy();

            return redirect()->to(base_url());
        }

        $identity = (new UserModel())->findAccessIdentityById($userId);
        if ($identity === null) {
            session()->destroy();

            return redirect()->to(base_url());
        }

        session()->set([
            'user_email' => $identity['email'],
            'user_name'  => trim($identity['nombre'] . ' ' . $identity['apellido']),
            'user_role'  => $identity['role'],
        ]);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
