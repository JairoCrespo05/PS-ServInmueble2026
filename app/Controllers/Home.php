<?php

namespace App\Controllers;

use App\Models\AdminDashboardModel;
use App\Models\FacturaModel;
use App\Models\OwnerDashboardModel;
use App\Models\RolModel;
use CodeIgniter\HTTP\ResponseInterface;

class Home extends BaseController
{
    public function home(): string|ResponseInterface
    {
        $user = [
            'id'       => session()->get('user_id'),
            'email'    => session()->get('user_email'),
            'nombre'   => session()->get('user_name'),
            'role'     => session()->get('user_role'),
            'requestStatus' => session()->get('user_request_status'),
        ];

        if ($user['role'] === RolModel::ROLE_PENDING) {
            return view('pending.php', ['user' => $user]);
        }

        if ($user['role'] === RolModel::ROLE_ADMINISTRATOR) {
            return view('home.php', [
                'user'            => $user,
                'isAdministrator' => true,
                'propertySummary' => (new AdminDashboardModel())->findPropertyServiceSummary(),
            ]);
        }

        if ($user['role'] === RolModel::ROLE_OWNER) {
            $invoices = new FacturaModel();

            return view('home.php', [
                'user'            => $user,
                'isAdministrator' => false,
                'recentInvoices'  => $invoices->findRecentForOwner((int) $user['id']),
                'overdueInvoices' => $invoices->findOverdueForOwner((int) $user['id']),
                'upcomingInvoice' => $invoices->findNearestUpcomingForOwner((int) $user['id']),
                'dashboardMetrics' => (new OwnerDashboardModel())->findMetricsForOwner((int) $user['id']),
            ]);
        }

        return $this->response->setStatusCode(403)->setBody('El rol de esta cuenta no tiene acceso al sistema.');
    }
}
