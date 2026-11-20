<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $allowedRoles = $arguments ?? [];
        if (in_array(session()->get('user_role'), $allowedRoles, true)) {
            return null;
        }

        return service('response')
            ->setStatusCode(403)
            ->setBody('No tenés permisos para realizar esta acción.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ?ResponseInterface
    {
        return null;
    }
}
