<?php

namespace App\Controllers;

use App\Models\AdminDashboardModel;
use App\Models\FacturaModel;
use App\Models\OwnerDashboardModel;
use App\Models\RolModel;
use CodeIgniter\Database\Exceptions\DatabaseException;
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
            $overduePageInput = $this->request->getGet('pagina_mora');
            $overduePage = is_string($overduePageInput) && ctype_digit($overduePageInput)
                ? max(1, (int) $overduePageInput)
                : 1;

            try {
                $dashboard = new AdminDashboardModel();
                $chartYears = $dashboard->findAvailableChartYears();
                $chartMetric = AdminDashboardModel::CHART_PROPERTIES;
                $chartYear = $chartYears[0];
                $viewData = [
                    'user'                 => $user,
                    'isAdministrator'      => true,
                    'propertySummary'      => $dashboard->findPropertyServiceSummary(),
                    'adminMetrics'         => $dashboard->findGlobalMetrics(),
                    'adminChartYears'      => $chartYears,
                    'adminChartYear'       => $chartYear,
                    'adminChartData'       => $dashboard->findMonthlyChartData($chartYear, $chartMetric),
                    'adminOverdueInvoices' => $dashboard->findOverdueInvoiceDetails($overduePage),
                ];
            } catch (DatabaseException $exception) {
                log_message('error', 'No se pudieron cargar los datos del dashboard de administración ({exception}).', [
                    'exception' => get_class($exception),
                ]);

                return $this->response
                    ->setStatusCode(503)
                    ->setBody(view('admin_dashboard_error.php', ['user' => $user]));
            }

            return view('home.php', $viewData);
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

    public function adminChart(): ResponseInterface
    {
        $yearInput = $this->request->getGet('year');
        $metricInput = $this->request->getGet('metric');
        $year = is_string($yearInput) && ctype_digit($yearInput) ? (int) $yearInput : null;
        $allowedMetrics = [
            AdminDashboardModel::CHART_PROPERTIES,
            AdminDashboardModel::CHART_OWNERS,
            AdminDashboardModel::CHART_ASSOCIATIONS,
            AdminDashboardModel::CHART_SERVICE_TYPES,
        ];

        if ($year === null || ! is_string($metricInput) || ! in_array($metricInput, $allowedMetrics, true)) {
            return $this->response
                ->setStatusCode(400)
                ->setJSON(['error' => 'Los parámetros del gráfico no son válidos.']);
        }

        try {
            $dashboard = new AdminDashboardModel();
            if (! in_array($year, $dashboard->findAvailableChartYears(), true)) {
                return $this->response
                    ->setStatusCode(400)
                    ->setJSON(['error' => 'El año seleccionado no tiene información disponible.']);
            }

            return $this->response->setJSON(
                $dashboard->findMonthlyChartData($year, $metricInput)
            );
        } catch (DatabaseException $exception) {
            log_message('error', 'No se pudo cargar la serie del dashboard de administración ({exception}).', [
                'exception' => get_class($exception),
            ]);

            return $this->response
                ->setStatusCode(503)
                ->setJSON(['error' => 'No se pudo cargar el gráfico. Intentá nuevamente.']);
        }
    }
}
