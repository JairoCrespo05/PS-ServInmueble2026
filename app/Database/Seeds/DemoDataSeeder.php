<?php

namespace App\Database\Seeds;

use App\Models\RolModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;
use DateTimeImmutable;
use RuntimeException;
use Throwable;

class DemoDataSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Demo2026!';

    public function run(): void
    {
        $environmentOverride = getenv('CI_ENVIRONMENT');
        if (ENVIRONMENT !== 'development'
            || ($environmentOverride !== false && $environmentOverride !== 'development')) {
            throw new RuntimeException('Los datos de demostración solo se pueden crear en development.');
        }

        $this->call('DevelopmentAdminSeeder');
        $this->call('ServiciosSeeder');

        $roles = [];
        foreach ([RolModel::ROLE_OWNER, RolModel::ROLE_PENDING] as $roleName) {
            $role = $this->db->table('roles')
                ->select('id')
                ->where('nombre', $roleName)
                ->get()
                ->getRowArray();

            if ($role === null) {
                throw new RuntimeException('No existe el rol ' . $roleName . '. Ejecutá primero las migraciones.');
            }

            $roles[$roleName] = (int) $role['id'];
        }

        if (! $this->db->transBegin()) {
            throw new RuntimeException('No se pudo iniciar la transacción para cargar los datos de demostración.');
        }

        try {
            $now = date('Y-m-d H:i:s');
            $luciaId = $this->ensureUser(
                'lucia.demo@example.test',
                'Lucía',
                'Fernández',
                RolModel::ROLE_OWNER,
                UserModel::REQUEST_APPROVED,
                $roles[RolModel::ROLE_OWNER],
                $now
            );
            $mateoId = $this->ensureUser(
                'mateo.demo@example.test',
                'Mateo',
                'Giménez',
                RolModel::ROLE_OWNER,
                UserModel::REQUEST_APPROVED,
                $roles[RolModel::ROLE_OWNER],
                $now
            );
            $this->ensureUser(
                'emilia.pendiente@example.test',
                'Emilia',
                'Torres',
                RolModel::ROLE_PENDING,
                UserModel::REQUEST_PENDING,
                $roles[RolModel::ROLE_PENDING],
                $now
            );

            $services = $this->ensureServices($now);
            $properties = [
                'casa' => $this->ensureProperty($luciaId, [
                    'alias_nombre' => 'Casa Los Aromos',
                    'tipo' => 'Casa',
                    'superficie' => '142.00',
                    'estado' => 'Disponible',
                    'estado_actividad' => 'Activo',
                    'calle' => 'Avenida del Parque',
                    'numero' => '1240',
                    'piso_dpto' => null,
                    'provincia' => 'Ciudad Autónoma de Buenos Aires',
                    'ciudad' => 'Buenos Aires',
                ], $now),
                'departamento' => $this->ensureProperty($luciaId, [
                    'alias_nombre' => 'Departamento Centro',
                    'tipo' => 'Departamento',
                    'superficie' => '78.50',
                    'estado' => 'Ocupado',
                    'estado_actividad' => 'Activo',
                    'calle' => 'Bartolomé Mitre',
                    'numero' => '456',
                    'piso_dpto' => '7 B',
                    'provincia' => 'Ciudad Autónoma de Buenos Aires',
                    'ciudad' => 'Buenos Aires',
                ], $now),
                'local' => $this->ensureProperty($luciaId, [
                    'alias_nombre' => 'Local en renovación',
                    'tipo' => 'Local comercial',
                    'superficie' => '116.00',
                    'estado' => 'En Mantenimiento',
                    'estado_actividad' => 'Activo',
                    'calle' => 'Avenida Santa Fe',
                    'numero' => '890',
                    'piso_dpto' => null,
                    'provincia' => 'Ciudad Autónoma de Buenos Aires',
                    'ciudad' => 'Buenos Aires',
                ], $now),
                'casa_inactiva' => $this->ensureProperty($luciaId, [
                    'alias_nombre' => 'Casa de temporada',
                    'tipo' => 'Casa',
                    'superficie' => '96.00',
                    'estado' => 'Disponible',
                    'estado_actividad' => 'Inactivo',
                    'calle' => 'Los Álamos',
                    'numero' => '320',
                    'piso_dpto' => null,
                    'provincia' => 'Río Negro',
                    'ciudad' => 'San Carlos de Bariloche',
                ], $now),
                'oficina' => $this->ensureProperty($mateoId, [
                    'alias_nombre' => 'Oficina Parque Industrial',
                    'tipo' => 'Oficina',
                    'superficie' => '104.00',
                    'estado' => 'Ocupado',
                    'estado_actividad' => 'Activo',
                    'calle' => 'Avenida Corrientes',
                    'numero' => '999',
                    'piso_dpto' => '3 A',
                    'provincia' => 'Ciudad Autónoma de Buenos Aires',
                    'ciudad' => 'Buenos Aires',
                ], $now),
            ];

            $associations = [
                'electricidad_casa' => $this->ensureAssociation(
                    $properties['casa'],
                    $services['electricidad'],
                    'Activo',
                    'Activo',
                    null,
                    'MED-ELA-1240',
                    'CTA-ELA-1240',
                    'Avenida del Parque 1240, Buenos Aires',
                    'Lucía Fernández',
                    $now
                ),
                'gas_casa' => $this->ensureAssociation(
                    $properties['casa'],
                    $services['gas'],
                    'Activo',
                    'Pausado',
                    null,
                    'MED-GAS-1240',
                    'CTA-GAS-1240',
                    'Avenida del Parque 1240, Buenos Aires',
                    'Lucía Fernández',
                    $now
                ),
                'agua_departamento' => $this->ensureAssociation(
                    $properties['departamento'],
                    $services['agua'],
                    'Activo',
                    'Activo',
                    null,
                    'MED-AGU-0456',
                    'CTA-AGU-0456',
                    'Bartolomé Mitre 456, Buenos Aires',
                    'Lucía Fernández',
                    $now
                ),
                'expensas_departamento' => $this->ensureAssociation(
                    $properties['departamento'],
                    $services['expensas'],
                    'Activo',
                    'Activo',
                    null,
                    null,
                    'EXP-DEP-0456',
                    'Bartolomé Mitre 456, Buenos Aires',
                    'Lucía Fernández',
                    $now
                ),
                'electricidad_local' => $this->ensureAssociation(
                    $properties['local'],
                    $services['electricidad'],
                    'Activo',
                    'Activo',
                    null,
                    'MED-ELA-0890',
                    'CTA-ELA-0890',
                    'Avenida Santa Fe 890, Buenos Aires',
                    'Lucía Fernández',
                    $now
                ),
                'internet_local_revision' => $this->ensureAssociation(
                    $properties['local'],
                    $services['internet'],
                    'Activo',
                    'En Revisión',
                    null,
                    null,
                    'CTA-INT-0890',
                    'Avenida Santa Fe 890, Buenos Aires',
                    'Lucía Fernández',
                    $now
                ),
                'internet_casa_inactiva' => $this->ensureAssociation(
                    $properties['casa_inactiva'],
                    $services['internet'],
                    'Inactivo',
                    'Activo',
                    'BajaInmueble',
                    null,
                    'CTA-INT-0320',
                    'Los Álamos 320, San Carlos de Bariloche',
                    'Lucía Fernández',
                    $now
                ),
                'agua_oficina' => $this->ensureAssociation(
                    $properties['oficina'],
                    $services['agua'],
                    'Activo',
                    'Activo',
                    null,
                    'MED-AGU-0999',
                    'CTA-AGU-0999',
                    'Avenida Corrientes 999, Buenos Aires',
                    'Mateo Giménez',
                    $now
                ),
                'expensas_oficina_baja' => $this->ensureAssociation(
                    $properties['oficina'],
                    $services['expensas'],
                    'Inactivo',
                    'Pausado',
                    null,
                    null,
                    'EXP-OFC-0999',
                    'Avenida Corrientes 999, Buenos Aires',
                    'Mateo Giménez',
                    $now
                ),
            ];

            $currentPeriod = new DateTimeImmutable('first day of this month');
            $previousPeriod = $currentPeriod->modify('-1 month');
            $olderPeriod = $currentPeriod->modify('-2 months');

            $this->ensureInvoice(
                $associations['electricidad_casa'],
                $this->invoice($previousPeriod, 'Pagada', '18450.00', '210.500', '1780.000', '1990.500')
            );
            $this->ensureInvoice(
                $associations['electricidad_casa'],
                $this->invoice($currentPeriod, 'Pendiente', '20120.00', '228.250', '1990.500', '2218.750')
            );
            $this->ensureInvoice(
                $associations['gas_casa'],
                $this->invoice($previousPeriod, 'Pendiente', '9720.00', '42.000', '310.000', '352.000')
            );
            $this->ensureInvoice(
                $associations['agua_departamento'],
                $this->invoice($currentPeriod, 'Pendiente', '6380.00', '18.700', '82.100', '100.800')
            );
            $this->ensureInvoice(
                $associations['expensas_departamento'],
                $this->invoice($previousPeriod, 'Pagada', '68500.00', null, null, null)
            );
            $this->ensureInvoice(
                $associations['internet_casa_inactiva'],
                $this->invoice($previousPeriod, 'Pagada', '18900.00', null, null, null)
            );
            $this->ensureInvoice(
                $associations['agua_oficina'],
                $this->invoice($currentPeriod, 'Pendiente', '11240.00', '33.600', '402.000', '435.600')
            );
            $this->ensureInvoice(
                $associations['expensas_oficina_baja'],
                $this->invoice($olderPeriod, 'Pagada', '53200.00', null, null, null)
            );

            if ($this->db->transStatus() === false || ! $this->db->transCommit()) {
                throw new RuntimeException('No se pudieron confirmar los datos de demostración.');
            }
        } catch (Throwable $exception) {
            $this->db->transRollback();
            throw $exception;
        }
    }

    private function ensureUser(
        string $email,
        string $name,
        string $surname,
        string $roleName,
        string $requestStatus,
        int $roleId,
        string $now
    ): int {
        $user = $this->db->table('usuarios')
            ->where('email', $email)
            ->get()
            ->getRowArray();

        if ($user !== null) {
            $assignedRoles = $this->db->table('rol_usuario ru')
                ->select('r.nombre')
                ->join('roles r', 'r.id = ru.rol_id')
                ->where('ru.usuario_id', $user['id'])
                ->get()
                ->getResultArray();

            if (
                $user['nombre'] !== $name
                || $user['apellido'] !== $surname
                || (int) $user['activo'] !== 1
                || $user['estado_solicitud'] !== $requestStatus
                || ! password_verify(self::DEMO_PASSWORD, (string) $user['password'])
                || count($assignedRoles) !== 1
                || $assignedRoles[0]['nombre'] !== $roleName
            ) {
                throw new RuntimeException('La cuenta reservada para la demo ya existe con datos distintos: ' . $email);
            }

            return (int) $user['id'];
        }

        $inserted = $this->db->table('usuarios')->insert([
            'nombre' => $name,
            'apellido' => $surname,
            'email' => $email,
            'password' => password_hash(self::DEMO_PASSWORD, PASSWORD_DEFAULT),
            'telefono' => null,
            'estado_solicitud' => $requestStatus,
            'activo' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $userId = (int) $this->db->insertID();

        if (! $inserted || $userId <= 0 || ! $this->db->table('rol_usuario')->insert([
            'usuario_id' => $userId,
            'rol_id' => $roleId,
        ])) {
            throw new RuntimeException('No se pudo crear la cuenta de demostración: ' . $email);
        }

        return $userId;
    }

    /**
     * @return array<string, int>
     */
    private function ensureServices(string $now): array
    {
        $catalog = [
            'electricidad' => ['nombre' => 'Electricidad', 'unidad_medida_std' => 'kWh', 'proveedor' => 'EDERSA', 'categoria' => 'Electricidad'],
            'agua' => ['nombre' => 'Agua corriente', 'unidad_medida_std' => 'm3', 'proveedor' => 'Aguas Rionegrinas S.A.', 'categoria' => 'Agua'],
            'gas' => ['nombre' => 'Gas natural', 'unidad_medida_std' => 'm3', 'proveedor' => 'Camuzzi Gas del Sur', 'categoria' => 'Gas'],
            'internet' => ['nombre' => 'Internet hogar', 'unidad_medida_std' => 'Mes', 'proveedor' => 'Río Net', 'categoria' => 'Internet'],
            'expensas' => ['nombre' => 'Expensas', 'unidad_medida_std' => 'Mes', 'proveedor' => 'Administración Demo', 'categoria' => 'Expensas'],
            'telefonia' => ['nombre' => 'Telefonía hogar', 'unidad_medida_std' => 'Mes', 'proveedor' => 'Telefonía Demo', 'categoria' => 'Telefonía'],
            'television' => ['nombre' => 'Televisión hogar', 'unidad_medida_std' => 'Mes', 'proveedor' => 'Televisión Demo', 'categoria' => 'Televisión'],
        ];
        $serviceIds = [];

        foreach ($catalog as $key => $service) {
            $matches = $this->db->table('servicios')
                ->where('nombre', $service['nombre'])
                ->where('proveedor', $service['proveedor'])
                ->where('categoria', $service['categoria'])
                ->get()
                ->getResultArray();

            if (count($matches) > 1) {
                throw new RuntimeException('Hay tipos de servicio duplicados para el proveedor ' . $service['proveedor'] . '.');
            }

            if ($matches !== []) {
                $this->assertFixtureMatches($matches[0], $service, 'tipo de servicio ' . $service['proveedor']);
                $serviceIds[$key] = (int) $matches[0]['id'];
                continue;
            }

            $service['created_at'] = $now;
            $service['updated_at'] = $now;
            if (! $this->db->table('servicios')->insert($service)) {
                throw new RuntimeException('No se pudo crear el tipo de servicio ' . $service['nombre'] . '.');
            }

            $serviceId = (int) $this->db->insertID();
            if ($serviceId <= 0) {
                throw new RuntimeException('No se pudo recuperar el ID del tipo de servicio ' . $service['nombre'] . '.');
            }
            $serviceIds[$key] = $serviceId;
        }

        return $serviceIds;
    }

    /**
     * @param array<string, mixed> $property
     */
    private function ensureProperty(int $ownerId, array $property, string $now): int
    {
        $existing = $this->db->table('inmuebles i')
            ->select('i.id, i.alias_nombre, i.tipo, i.superficie, i.estado, i.estado_actividad, d.calle, d.numero, d.piso_dpto, d.provincia, d.ciudad')
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->where('i.usuario_id', $ownerId)
            ->where('i.alias_nombre', $property['alias_nombre'])
            ->get()
            ->getResultArray();

        if (count($existing) > 1) {
            throw new RuntimeException('Hay inmuebles duplicados con el alias ' . $property['alias_nombre'] . '.');
        }

        if ($existing !== []) {
            $this->assertFixtureMatches($existing[0], $property, 'inmueble ' . $property['alias_nombre'], ['superficie']);

            return (int) $existing[0]['id'];
        }

        $address = [
            'calle' => $property['calle'],
            'numero' => $property['numero'],
            'piso_dpto' => $property['piso_dpto'],
            'provincia' => $property['provincia'],
            'ciudad' => $property['ciudad'],
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if (! $this->db->table('direcciones')->insert($address)) {
            throw new RuntimeException('No se pudo crear la dirección del inmueble ' . $property['alias_nombre'] . '.');
        }
        $addressId = (int) $this->db->insertID();

        $inmueble = [
            'alias_nombre' => $property['alias_nombre'],
            'tipo' => $property['tipo'],
            'superficie' => $property['superficie'],
            'estado' => $property['estado'],
            'estado_actividad' => $property['estado_actividad'],
            'usuario_id' => $ownerId,
            'direccion_id' => $addressId,
            'created_at' => $now,
            'updated_at' => $now,
        ];
        if ($addressId <= 0 || ! $this->db->table('inmuebles')->insert($inmueble)) {
            throw new RuntimeException('No se pudo crear el inmueble ' . $property['alias_nombre'] . '.');
        }

        $propertyId = (int) $this->db->insertID();
        if ($propertyId <= 0) {
            throw new RuntimeException('No se pudo recuperar el ID del inmueble ' . $property['alias_nombre'] . '.');
        }

        return $propertyId;
    }

    private function ensureAssociation(
        int $propertyId,
        int $serviceId,
        string $state,
        string $operationalState,
        ?string $suspensionReason,
        ?string $meterNumber,
        ?string $accountNumber,
        string $billingAddress,
        string $holder,
        string $now
    ): int {
        $fixture = [
            'inmueble_id' => $propertyId,
            'servicio_id' => $serviceId,
            'nro_medidor' => $meterNumber,
            'titular_inmueble' => $holder,
            'estado' => $state,
            'estado_vinculacion' => $operationalState,
            'motivo_suspension' => $suspensionReason,
            'nro_cuenta_contrato' => $accountNumber,
            'cod_pago_electronico' => null,
            'domicilio_facturacion' => $billingAddress,
        ];
        $matches = $this->db->table('inmueble_servicios')
            ->where('inmueble_id', $propertyId)
            ->where('servicio_id', $serviceId)
            ->get()
            ->getResultArray();

        if (count($matches) > 1) {
            throw new RuntimeException('Hay vínculos duplicados para el inmueble y tipo de servicio de la demo.');
        }

        if ($matches !== []) {
            $this->assertFixtureMatches($matches[0], $fixture, 'vínculo de servicio');

            return (int) $matches[0]['id'];
        }

        $fixture['created_at'] = $now;
        $fixture['updated_at'] = $now;
        if (! $this->db->table('inmueble_servicios')->insert($fixture)) {
            throw new RuntimeException('No se pudo crear un vínculo de servicio de la demo.');
        }

        $associationId = (int) $this->db->insertID();
        if ($associationId <= 0) {
            throw new RuntimeException('No se pudo recuperar el ID del vínculo de servicio de la demo.');
        }

        return $associationId;
    }

    /**
     * @return array<string, mixed>
     */
    private function invoice(
        DateTimeImmutable $period,
        string $state,
        string $amount,
        ?string $consumption,
        ?string $previousReading,
        ?string $currentReading
    ): array {
        $paid = $state === 'Pagada';
        $paymentDate = $paid ? $period->modify('+15 days')->format('Y-m-d') : null;

        return [
            'periodo_facturacion' => $period->format('Y-m-01'),
            'fecha_emision' => $period->format('Y-m-d'),
            'fecha_primer_vencimiento' => $period->modify('+20 days')->format('Y-m-d'),
            'fecha_segundo_vencimiento' => null,
            'monto_total' => $amount,
            'consumo_periodo' => $consumption,
            'lectura_anterior' => $previousReading,
            'lectura_actual' => $currentReading,
            'estado_factura' => $state,
            'fecha_pago_realizado' => $paymentDate,
            'numero_comprobante' => null,
            'medio_pago' => $paid ? 'Transferencia' : null,
            'monto_abonado' => $paid ? $amount : null,
            'mora' => null,
        ];
    }

    /**
     * @param array<string, mixed> $invoice
     */
    private function ensureInvoice(int $associationId, array $invoice): void
    {
        $invoice['inmueble_servicio_id'] = $associationId;
        $existing = $this->db->table('facturas')
            ->where('inmueble_servicio_id', $associationId)
            ->where('periodo_facturacion', $invoice['periodo_facturacion'])
            ->get()
            ->getRowArray();

        if ($existing !== null) {
            $this->assertFixtureMatches($existing, $invoice, 'factura del período ' . $invoice['periodo_facturacion'], [
                'monto_total',
                'consumo_periodo',
                'lectura_anterior',
                'lectura_actual',
                'monto_abonado',
            ]);

            return;
        }

        $now = date('Y-m-d H:i:s');
        $invoice['created_at'] = $now;
        $invoice['updated_at'] = $now;
        if (! $this->db->table('facturas')->insert($invoice)) {
            throw new RuntimeException('No se pudo crear una factura de demostración.');
        }
    }

    /**
     * @param array<string, mixed> $actual
     * @param array<string, mixed> $expected
     * @param list<string> $decimalFields
     */
    private function assertFixtureMatches(
        array $actual,
        array $expected,
        string $entity,
        array $decimalFields = []
    ): void {
        foreach ($expected as $field => $expectedValue) {
            $actualValue = $actual[$field] ?? null;
            if ($expectedValue === null) {
                $matches = $actualValue === null;
            } elseif (in_array($field, $decimalFields, true)) {
                $matches = $actualValue !== null
                    && abs((float) $actualValue - (float) $expectedValue) < 0.0001;
            } else {
                $matches = (string) $actualValue === (string) $expectedValue;
            }

            if (! $matches) {
                throw new RuntimeException('El registro reservado para la demo no coincide con la semilla: ' . $entity . '.');
            }
        }
    }
}
