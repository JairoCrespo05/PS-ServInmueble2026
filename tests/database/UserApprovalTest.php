<?php

use App\Filters\RoleFilter;
use App\Models\RolModel;
use App\Models\UserModel;
use CodeIgniter\Security\Exceptions\SecurityException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class UserApprovalTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $namespace = 'App';

    public function testRegistrationRemainsPendingUntilAdministratorApprovesIt(): void
    {
        $users = new UserModel();
        $result = $users->registerWithDefaultRole([
            'nombre'    => 'Usuario',
            'apellido'  => 'De Prueba',
            'email'     => 'pending@example.test',
            'password'  => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono'  => '',
            'rol_id'    => (new RolModel())->getIdByName(RolModel::ROLE_ADMINISTRATOR),
        ]);

        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $result['status']);
        $this->assertCount(1, $users->findPendingApprovals());

        $userId = $result['userId'];
        $identity = $users->findAccessIdentityById($userId);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_PENDING, $identity['role']);
        $this->assertSame(UserModel::REQUEST_PENDING, $identity['estado_solicitud']);
        $this->assertSame(UserModel::APPROVAL_SUCCESS, $users->approvePendingUser($userId, RolModel::ROLE_OWNER));
        $identity = $users->findAccessIdentityById($userId);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_OWNER, $identity['role']);
        $this->assertSame(UserModel::REQUEST_APPROVED, $identity['estado_solicitud']);
        $this->assertSame(
            UserModel::APPROVAL_NOT_PENDING,
            $users->approvePendingUser($userId, RolModel::ROLE_OWNER)
        );
        $this->assertSame([], $users->findPendingApprovals());
    }

    public function testInvalidCredentialsUseTheSameFailureStatus(): void
    {
        $users = new UserModel();
        $users->registerWithDefaultRole([
            'nombre'   => 'Usuario',
            'apellido' => 'De Prueba',
            'email'    => 'credentials@example.test',
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ]);

        $unknownEmail = $users->authenticate('missing@example.test', 'wrong');
        $wrongPassword = $users->authenticate('credentials@example.test', 'wrong');

        $this->assertSame(UserModel::LOGIN_INVALID_CREDENTIALS, $unknownEmail['status']);
        $this->assertSame($unknownEmail['status'], $wrongPassword['status']);
    }

    public function testDuplicateEmailRegistrationIsRejected(): void
    {
        $users = new UserModel();
        $userData = [
            'nombre'   => 'Usuario',
            'apellido' => 'De Prueba',
            'email'    => 'duplicate@example.test',
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ];

        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $users->registerWithDefaultRole($userData)['status']);
        $this->assertSame(
            UserModel::REGISTRATION_EMAIL_EXISTS,
            $users->registerWithDefaultRole($userData)['status']
        );
    }

    public function testPublicSignupIgnoresSubmittedAdministratorRole(): void
    {
        $signupPage = $this->get('sing_up');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $signupPage->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);

        $response = $this->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('sing_up', [
                'first_name'       => 'Nueva',
                'last_name'        => 'Persona',
                'email'            => 'public-signup@example.test',
                'phone'            => '',
                'password'         => 'GoodPass1!',
                'confirm_password' => 'GoodPass1!',
                'terms'            => '1',
                'role'             => RolModel::ROLE_ADMINISTRATOR,
                'estado_solicitud' => UserModel::REQUEST_APPROVED,
                'csrf_test_name'   => $tokenMatches[1],
            ]);

        $response->assertRedirectTo(base_url('home'));

        $user = (new UserModel())->where('email', 'public-signup@example.test')->first();
        $this->assertNotNull($user);
        $identity = (new UserModel())->findAccessIdentityById((int) $user['id']);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_PENDING, $identity['role']);
        $this->assertSame(UserModel::REQUEST_PENDING, $identity['estado_solicitud']);
        $this->assertSame((int) $user['id'], session()->get('user_id'));
    }

    public function testLoginRegeneratesSessionIdForPendingUser(): void
    {
        $user = $this->registerTestUser('login-session@example.test');
        $loginPage = $this->get('/');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $loginPage->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);
        $session = service('session');
        $session->didRegenerate = false;

        $response = $this->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('login', [
                'email'          => 'login-session@example.test',
                'password'       => 'Passw0rd!',
                'csrf_test_name' => $tokenMatches[1],
            ]);

        $response->assertRedirectTo(base_url('home'));
        $this->assertTrue($session->didRegenerate);
        $this->assertSame($user['userId'], session()->get('user_id'));
    }

    public function testLogoutRequiresCsrfAndRedirectsToLogin(): void
    {
        $user = $this->registerTestUser('logout-session@example.test');
        $home = $this->withSession(['user_id' => $user['userId']])->get('home');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $home->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);

        $response = $this->withSession(['user_id' => $user['userId']])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('logout', ['csrf_test_name' => $tokenMatches[1]]);

        $response->assertRedirectTo(base_url());
    }

    public function testApprovalRejectsUnknownUsers(): void
    {
        $this->assertSame(
            UserModel::APPROVAL_NOT_PENDING,
            (new UserModel())->approvePendingUser(999999, RolModel::ROLE_OWNER)
        );
    }

    public function testPendingUserSeesOnlyTheWaitingScreenAndRoleRefreshesAfterApproval(): void
    {
        $result = $this->registerTestUser('pending-home@example.test');
        $userId = $result['userId'];

        $pendingHome = $this->withSession(['user_id' => $userId])->get('home');
        $pendingHome->assertOK();
        $pendingHome->assertSee('Tu cuenta está pendiente de aprobación');
        $pendingHome->assertDontSee('Inmuebles (ABMC)');

        $users = new UserModel();
        $this->assertSame(UserModel::APPROVAL_SUCCESS, $users->approvePendingUser($userId, RolModel::ROLE_OWNER));

        $ownerHome = $this->withSession(['user_id' => $userId])->get('home');
        $ownerHome->assertOK();
        $ownerHome->assertSee('Propietario');
        $ownerHome->assertDontSee('Solicitudes de acceso');
        $ownerHome->assertDontSee('Gestión de usuarios');
    }

    public function testHomeRequiresAnAuthenticatedSession(): void
    {
        $response = $this->withSession([])->get('home');

        $response->assertRedirectTo(base_url());
    }

    public function testOwnerCannotPassTheAdministratorRoleFilter(): void
    {
        session()->set('user_role', RolModel::ROLE_OWNER);

        $response = (new RoleFilter())->before(service('request'), [RolModel::ROLE_ADMINISTRATOR]);

        $this->assertSame(403, $response->getStatusCode());
    }

    public function testAdministratorHomeShowsOnlyAdministrativeFunctionsAndUsersHaveSeparateGetPage(): void
    {
        $administrator = $this->registerTestUser('administrator@example.test');
        $pendingUser = $this->registerTestUser('waiting@example.test');
        $owner = $this->registerTestUser('owner@example.test');
        $this->db->table('usuarios')
            ->where('id', $pendingUser['userId'])
            ->update(['telefono' => '11 5555-0101']);

        $administratorRoleId = (new RolModel())->getIdByName(RolModel::ROLE_ADMINISTRATOR);

        $this->db->table('rol_usuario')
            ->where('usuario_id', $administrator['userId'])
            ->update(['rol_id' => $administratorRoleId]);
        $this->db->table('usuarios')
            ->where('id', $administrator['userId'])
            ->update(['estado_solicitud' => UserModel::REQUEST_APPROVED]);
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER)
        );

        $response = $this->withSession(['user_id' => $administrator['userId']])->get('home');
        $body = $response->response()->getBody();

        $response->assertOK();
        $response->assertSee('Administración del sistema');
        $response->assertSee('Gestión de usuarios');
        $response->assertSee('Tipos de servicios');
        $response->assertDontSee('Inmuebles (ABMC)');
        $response->assertDontSee('Facturación y Vencimientos Recientes');
        $response->assertDontSee('Torre Bella Vista');
        $response->assertDontSee('Listado general de usuarios');

        $usersResponse = $this->withSession(['user_id' => $administrator['userId']])->get('usuarios');
        $usersBody = $usersResponse->response()->getBody();
        $usersResponse->assertOK();
        $usersResponse->assertSee('Solicitudes pendientes');
        $usersResponse->assertSee('Listado general de usuarios');
        $this->assertSame(1, preg_match('/<table[^>]*id="pending-users-table"[^>]*>(.*?)<\/table>/s', $usersBody, $pendingTable));
        $this->assertSame(1, preg_match('/<table[^>]*id="all-users-table"[^>]*>(.*?)<\/table>/s', $usersBody, $allUsersTable));
        $this->assertStringContainsString('waiting@example.test', $pendingTable[1]);
        $this->assertStringContainsString('11 5555-0101', $pendingTable[1]);
        $this->assertStringNotContainsString('administrator@example.test', $pendingTable[1]);
        $this->assertStringNotContainsString('owner@example.test', $pendingTable[1]);
        $this->assertStringContainsString('administrator@example.test', $allUsersTable[1]);
        $this->assertStringContainsString('waiting@example.test', $allUsersTable[1]);
        $this->assertStringContainsString('owner@example.test', $allUsersTable[1]);
        $this->assertStringContainsString(RolModel::ROLE_ADMINISTRATOR, $allUsersTable[1]);
        $this->assertStringContainsString(RolModel::ROLE_PENDING, $allUsersTable[1]);
        $this->assertStringContainsString(RolModel::ROLE_OWNER, $allUsersTable[1]);
    }

    public function testAdministratorApprovesPendingUserThroughProtectedCsrfRoute(): void
    {
        $administrator = $this->registerTestUser('approver@example.test');
        $pendingUser = $this->registerTestUser('to-approve@example.test');
        $administratorRoleId = (new RolModel())->getIdByName(RolModel::ROLE_ADMINISTRATOR);

        $this->db->table('rol_usuario')
            ->where('usuario_id', $administrator['userId'])
            ->update(['rol_id' => $administratorRoleId]);
        $this->db->table('usuarios')
            ->where('id', $administrator['userId'])
            ->update(['estado_solicitud' => UserModel::REQUEST_APPROVED]);

        $userList = $this->withSession(['user_id' => $administrator['userId']])->get('usuarios');
        $userList->assertSee('value="Administrador"');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $userList->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);

        $response = $this->withSession(['user_id' => $administrator['userId']])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post(
                'admin/solicitudes/' . $pendingUser['userId'] . '/aprobar',
                [
                    'role'           => RolModel::ROLE_ADMINISTRATOR,
                    'csrf_test_name' => $tokenMatches[1],
                ]
            );

        $response->assertRedirectTo(site_url('usuarios'));
        $identity = (new UserModel())->findAccessIdentityById($pendingUser['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_ADMINISTRATOR, $identity['role']);
        $this->assertSame(UserModel::REQUEST_APPROVED, $identity['estado_solicitud']);
    }

    public function testOwnerCannotOpenAdministratorUserList(): void
    {
        $owner = $this->registerTestUser('owner-cannot-list@example.test');
        (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER);

        $response = $this->withSession(['user_id' => $owner['userId']])->get('usuarios');

        $response->assertStatus(403);
    }

    public function testRejectedRequestCanBeReopenedAndThenApprovedAsAdministrator(): void
    {
        $user = $this->registerTestUser('rejected@example.test');
        $users = new UserModel();

        $this->assertSame(UserModel::REQUEST_REJECTION_SUCCESS, $users->rejectPendingUser($user['userId']));
        $identity = $users->findAccessIdentityById($user['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(UserModel::REQUEST_REJECTED, $identity['estado_solicitud']);
        $this->assertSame(RolModel::ROLE_PENDING, $identity['role']);
        $this->assertSame([], $users->findPendingApprovals());

        $rejectedHome = $this->withSession(['user_id' => $user['userId']])->get('home');
        $rejectedHome->assertOK();
        $rejectedHome->assertSee('Tu solicitud no fue aprobada');
        $rejectedHome->assertDontSee('Inmuebles (ABMC)');

        $this->assertSame(UserModel::REQUEST_REOPEN_SUCCESS, $users->reopenRejectedRequest($user['userId']));
        $this->assertSame(1, count($users->findPendingApprovals()));
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            $users->approvePendingUser($user['userId'], RolModel::ROLE_ADMINISTRATOR)
        );

        $identity = $users->findAccessIdentityById($user['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_ADMINISTRATOR, $identity['role']);
        $this->assertSame(UserModel::REQUEST_APPROVED, $identity['estado_solicitud']);
    }

    public function testAdministratorRejectsAndReopensRequestsThroughProtectedPostRoutes(): void
    {
        $administrator = $this->registerTestUser('reject-admin@example.test');
        $pendingUser = $this->registerTestUser('reject-user@example.test');
        $this->assignRole($administrator['userId'], RolModel::ROLE_ADMINISTRATOR);

        $userList = $this->withSession(['user_id' => $administrator['userId']])->get('usuarios');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $userList->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);

        $rejected = $this->withSession(['user_id' => $administrator['userId']])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('admin/solicitudes/' . $pendingUser['userId'] . '/rechazar', [
                'csrf_test_name' => $tokenMatches[1],
            ]);
        $rejected->assertRedirectTo(site_url('usuarios'));

        $identity = (new UserModel())->findAccessIdentityById($pendingUser['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(UserModel::REQUEST_REJECTED, $identity['estado_solicitud']);

        $userList = $this->withSession(['user_id' => $administrator['userId']])->get('usuarios');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $userList->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);
        $reopened = $this->withSession(['user_id' => $administrator['userId']])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('admin/solicitudes/' . $pendingUser['userId'] . '/reabrir', [
                'csrf_test_name' => $tokenMatches[1],
            ]);
        $reopened->assertRedirectTo(site_url('usuarios'));

        $identity = (new UserModel())->findAccessIdentityById($pendingUser['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(UserModel::REQUEST_PENDING, $identity['estado_solicitud']);
    }

    public function testAdministratorCanModifyApprovedUserDataAndRole(): void
    {
        $administrator = $this->registerTestUser('profile-admin@example.test');
        $owner = $this->registerTestUser('profile-owner@example.test');
        $this->assignRole($administrator['userId'], RolModel::ROLE_ADMINISTRATOR);
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER)
        );

        $response = $this->withSession(['user_id' => $administrator['userId']])->get(
            'usuarios/' . $owner['userId'] . '/editar'
        );
        $response->assertOK();
        $response->assertSee('name="role"');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $response->response()->getBody(), $tokenMatches);
        $this->assertNotEmpty($tokenMatches[1] ?? null);

        $updated = $this->withSession(['user_id' => $administrator['userId']])
            ->withHeaders(['Cookie' => 'csrf_cookie_name=' . $tokenMatches[1]])
            ->post('usuarios/' . $owner['userId'], [
                'nombre'          => 'Propietaria',
                'apellido'        => 'Actualizada',
                'email'           => 'profile-owner-updated@example.test',
                'telefono'        => '11 5555-0102',
                'role'            => RolModel::ROLE_ADMINISTRATOR,
                'csrf_test_name'  => $tokenMatches[1],
            ]);

        $updated->assertRedirectTo(site_url('usuarios'));
        $identity = (new UserModel())->findAccessIdentityById($owner['userId']);
        $this->assertNotNull($identity);
        $this->assertSame('Propietaria', $identity['nombre']);
        $this->assertSame('profile-owner-updated@example.test', $identity['email']);
        $this->assertSame(RolModel::ROLE_ADMINISTRATOR, $identity['role']);
    }

    public function testSoftDeletingUserPreservesAssociatedPropertiesAndBlocksAccess(): void
    {
        $administrator = $this->registerTestUser('property-admin@example.test');
        $owner = $this->registerTestUser('property-owner@example.test');
        $this->assignRole($administrator['userId'], RolModel::ROLE_ADMINISTRATOR);
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER)
        );

        $this->db->table('direcciones')->insert([
            'calle'  => 'Calle Ficticia',
            'numero' => '123',
            'ciudad' => 'Ciudad Prueba',
        ]);
        $addressId = (int) $this->db->insertID();
        $this->db->table('inmuebles')->insert([
            'alias_nombre' => 'Casa de prueba',
            'tipo'         => 'Casa',
            'superficie'   => 80,
            'estado'       => 'Activo',
            'usuario_id'   => $owner['userId'],
            'direccion_id' => $addressId,
        ]);
        $propertyId = (int) $this->db->insertID();
        $this->db->table('servicios')->insert([
            'nombre'            => 'Servicio histórico de prueba',
            'unidad_medida_std' => 'kWh',
            'proveedor'         => 'Proveedor de prueba',
            'categoria'         => 'Electricidad',
        ]);
        $serviceId = (int) $this->db->insertID();
        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id'      => $propertyId,
            'servicio_id'      => $serviceId,
            'titular_inmueble' => 'Titular de prueba',
            'estado'           => 'Inactivo',
        ]);

        $users = new UserModel();
        $this->assertSame(
            UserModel::USER_DEACTIVATION_SUCCESS,
            $users->deactivateUser($owner['userId'], $administrator['userId'])
        );
        $identity = $users->findAccessIdentityById($owner['userId']);
        $this->assertNotNull($identity);
        $this->assertFalse($identity['activo']);
        $this->assertCount(1, $users->findPropertiesForUser($owner['userId']));
        $propertyPage = $this->withSession(['user_id' => $administrator['userId']])
            ->get('usuarios/' . $owner['userId'] . '/inmuebles');
        $propertyPage->assertOK();
        $propertyPage->assertSee('Casa de prueba');
        $propertyPage->assertSee('Servicio histórico de prueba');
        $propertyPage->assertSee('De baja');
        $propertyPage->assertSee('Estado operativo: Desconocido');
        $this->assertSame(
            UserModel::LOGIN_INVALID_CREDENTIALS,
            $users->authenticate('property-owner@example.test', 'Passw0rd!')['status']
        );
        $blockedHome = $this->withSession(['user_id' => $owner['userId']])->get('home');
        $blockedHome->assertRedirectTo(base_url());
    }

    public function testAdministratorHomeSummarizesPropertiesUsersAndServicesIncludingDeactivatedUsers(): void
    {
        $administrator = $this->registerTestUser('summary-admin@example.test');
        $owner = $this->registerTestUser('summary-owner@example.test');
        $this->assignRole($administrator['userId'], RolModel::ROLE_ADMINISTRATOR);
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER)
        );

        $this->db->table('direcciones')->insert([
            'calle'  => 'Calle del Resumen',
            'numero' => '789',
            'ciudad' => 'Ciudad Prueba',
        ]);
        $addressId = (int) $this->db->insertID();
        $this->db->table('inmuebles')->insert([
            'alias_nombre' => 'Inmueble del resumen',
            'tipo'         => 'Casa',
            'superficie'   => 80,
            'estado'       => 'Inactivo',
            'usuario_id'   => $owner['userId'],
            'direccion_id' => $addressId,
        ]);
        $propertyId = (int) $this->db->insertID();
        $this->db->table('servicios')->insert([
            'nombre'            => 'Servicio del resumen',
            'unidad_medida_std' => 'kWh',
            'proveedor'         => 'Proveedor de prueba',
            'categoria'         => 'Electricidad',
        ]);
        $serviceId = (int) $this->db->insertID();
        $this->db->table('inmueble_servicios')->insert([
            'inmueble_id'      => $propertyId,
            'servicio_id'      => $serviceId,
            'titular_inmueble' => 'Titular de prueba',
            'estado'           => 'Inactivo',
        ]);
        $this->assertSame(
            UserModel::USER_DEACTIVATION_SUCCESS,
            (new UserModel())->deactivateUser($owner['userId'], $administrator['userId'])
        );

        $response = $this->withSession(['user_id' => $administrator['userId']])->get('home');
        $response->assertOK();
        $response->assertSee('Inmuebles, usuarios y servicios');
        $response->assertSee('id="adminSummaryServiceFilter"', false);
        $response->assertSee('id="adminSummaryPropertyStateFilter"', false);
        $response->assertSee('id="adminSummaryAccountStateFilter"', false);
        $response->assertSee('Inmueble del resumen');
        $response->assertSee('Usuario De Prueba');
        $response->assertSee('Baja');
        $response->assertSee('Servicio del resumen');
        $response->assertSee('Inactivo');
    }

    public function testUnassignedPropertyCanBeLinkedToAnOwnerAndListed(): void
    {
        $owner = $this->registerTestUser('unassigned-property-owner@example.test');
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER)
        );

        $this->db->table('direcciones')->insert([
            'calle'  => 'Calle Ficticia',
            'numero' => '456',
            'ciudad' => 'Ciudad Prueba',
        ]);
        $addressId = (int) $this->db->insertID();
        $this->db->table('inmuebles')->insert([
            'alias_nombre' => 'Inmueble sin asignar',
            'tipo'         => 'Casa',
            'superficie'   => 90,
            'estado'       => 'Activo',
            'usuario_id'   => null,
            'direccion_id' => $addressId,
        ]);
        $propertyId = (int) $this->db->insertID();

        $users = new UserModel();
        $this->assertCount(0, $users->findPropertiesForUser($owner['userId']));

        $this->db->table('inmuebles')
            ->where('id', $propertyId)
            ->update(['usuario_id' => $owner['userId']]);

        $properties = $users->findPropertiesForUser($owner['userId']);
        $this->assertCount(1, $properties);
        $this->assertSame('Inmueble sin asignar', $properties[0]['alias_nombre']);
    }

    public function testMutatingRouteRejectsMissingCsrfToken(): void
    {
        $this->expectException(SecurityException::class);

        $this->withSession([])->post('sing_up');
    }

    /**
     * @return array{status: string, userId: int}
     */
    private function registerTestUser(string $email): array
    {
        $result = (new UserModel())->registerWithDefaultRole([
            'nombre'   => 'Usuario',
            'apellido' => 'De Prueba',
            'email'    => $email,
            'password' => password_hash('Passw0rd!', PASSWORD_DEFAULT),
            'telefono' => '',
        ]);

        $this->assertSame(UserModel::REGISTRATION_SUCCESS, $result['status']);

        return $result;
    }

    private function assignRole(int $userId, string $roleName): void
    {
        $roleId = (new RolModel())->getIdByName($roleName);
        $this->assertNotNull($roleId);

        $this->db->table('rol_usuario')
            ->where('usuario_id', $userId)
            ->update(['rol_id' => $roleId]);
        $this->db->table('usuarios')
            ->where('id', $userId)
            ->update(['estado_solicitud' => UserModel::REQUEST_APPROVED]);
    }
}
