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
        $this->assertSame(UserModel::APPROVAL_SUCCESS, $users->approvePendingUser($userId, RolModel::ROLE_OWNER));
        $identity = $users->findAccessIdentityById($userId);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_OWNER, $identity['role']);
        $this->assertSame(
            UserModel::APPROVAL_NOT_PENDING,
            $users->approvePendingUser($userId, RolModel::ROLE_OWNER)
        );
        $this->assertSame([], $users->findPendingApprovals());
    }

    public function testApprovalRejectsRolesOutsideAdministratorAndOwner(): void
    {
        $user = $this->registerTestUser('invalid-approval-role@example.test');

        $this->assertSame(
            UserModel::APPROVAL_INVALID_ROLE,
            (new UserModel())->approvePendingUser($user['userId'], RolModel::ROLE_PENDING)
        );
        $identity = (new UserModel())->findAccessIdentityById($user['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_PENDING, $identity['role']);
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
                'csrf_test_name'   => $tokenMatches[1],
            ]);

        $response->assertRedirectTo(base_url('home'));

        $user = (new UserModel())->where('email', 'public-signup@example.test')->first();
        $this->assertNotNull($user);
        $identity = (new UserModel())->findAccessIdentityById((int) $user['id']);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_PENDING, $identity['role']);
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

    public function testAdministratorHomeShowsSharedHomeAndSeparateUserLists(): void
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
        $this->assertSame(
            UserModel::APPROVAL_SUCCESS,
            (new UserModel())->approvePendingUser($owner['userId'], RolModel::ROLE_OWNER)
        );

        $response = $this->withSession(['user_id' => $administrator['userId']])->get('home');
        $body = $response->response()->getBody();

        $response->assertOK();
        $response->assertSee('Resumen y Control Operativo');
        $response->assertSee('Gestión de usuarios');
        $response->assertSee('Solicitudes pendientes');
        $response->assertSee('Listado general de usuarios');
        $this->assertSame(1, preg_match('/<table[^>]*id="pending-users-table"[^>]*>(.*?)<\/table>/s', $body, $pendingTable));
        $this->assertSame(1, preg_match('/<table[^>]*id="all-users-table"[^>]*>(.*?)<\/table>/s', $body, $allUsersTable));
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

        $adminHome = $this->withSession(['user_id' => $administrator['userId']])->get('home');
        $adminHome->assertSee('value="Propietario"');
        $adminHome->assertSee('value="Administrador"');
        preg_match('/name="csrf_test_name" value="([^"]+)"/', $adminHome->response()->getBody(), $tokenMatches);
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

        $response->assertRedirectTo(base_url('home'));
        $identity = (new UserModel())->findAccessIdentityById($pendingUser['userId']);
        $this->assertNotNull($identity);
        $this->assertSame(RolModel::ROLE_ADMINISTRATOR, $identity['role']);
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
}
