<?php 

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    public const REGISTRATION_SUCCESS      = 'success';
    public const REGISTRATION_EMAIL_EXISTS = 'email_exists';
    public const REGISTRATION_ROLE_MISSING = 'role_missing';
    public const REGISTRATION_FAILED       = 'failed';
    public const LOGIN_SUCCESS             = 'login_success';
    public const LOGIN_INVALID_CREDENTIALS  = 'login_invalid_credentials';
    public const LOGIN_ROLE_MISSING         = 'login_role_missing';
    public const APPROVAL_SUCCESS           = 'approval_success';
    public const APPROVAL_NOT_PENDING       = 'approval_not_pending';
    public const APPROVAL_ROLE_MISSING      = 'approval_role_missing';
    public const APPROVAL_INVALID_ROLE      = 'approval_invalid_role';
    public const APPROVAL_FAILED            = 'approval_failed';
    public const REQUEST_PENDING             = 'Pendiente';
    public const REQUEST_APPROVED            = 'Aprobada';
    public const REQUEST_REJECTED            = 'Rechazada';
    public const USER_UPDATE_SUCCESS         = 'user_update_success';
    public const USER_UPDATE_NOT_FOUND       = 'user_update_not_found';
    public const USER_UPDATE_INVALID_ROLE    = 'user_update_invalid_role';
    public const USER_UPDATE_INVALID_STATE   = 'user_update_invalid_state';
    public const USER_UPDATE_SELF_ROLE_CHANGE  = 'user_update_self_role_change';
    public const USER_UPDATE_LAST_ADMIN        = 'user_update_last_admin';
    public const USER_UPDATE_FAILED            = 'user_update_failed';
    public const USER_DEACTIVATION_SUCCESS     = 'user_deactivation_success';
    public const USER_DEACTIVATION_NOT_FOUND   = 'user_deactivation_not_found';
    public const USER_DEACTIVATION_SELF        = 'user_deactivation_self';
    public const USER_DEACTIVATION_LAST_ADMIN  = 'user_deactivation_last_admin';
    public const USER_DEACTIVATION_FAILED      = 'user_deactivation_failed';
    public const REQUEST_REJECTION_SUCCESS     = 'request_rejection_success';
    public const REQUEST_REJECTION_NOT_PENDING = 'request_rejection_not_pending';
    public const REQUEST_REOPEN_SUCCESS        = 'request_reopen_success';
    public const REQUEST_REOPEN_NOT_REJECTED   = 'request_reopen_not_rejected';

    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'nombre',
        'apellido',
        'email',
        'password',
        'telefono',
        'direccion_id',
        'estado_solicitud',
    ];
    protected $useTimestamps = true;

    public function authenticate(string $email, string $password): array
    {
        $user = $this->where('email', $email)->first();

        if ($user === null || ! password_verify($password, $user['password'])) {
            return ['status' => self::LOGIN_INVALID_CREDENTIALS];
        }

        $identity = $this->findAccessIdentityById((int) $user['id']);
        if ($identity === null) {
            return ['status' => self::LOGIN_ROLE_MISSING];
        }
        if (! $identity['activo']) {
            return ['status' => self::LOGIN_INVALID_CREDENTIALS];
        }

        return [
            'status' => self::LOGIN_SUCCESS,
            'user'   => $identity,
        ];
    }

    /**
     * Load current access data from the database instead of trusting role data in a session.
     *
     * @return array{id: int, email: string, nombre: string, apellido: string, role: string, estado_solicitud: string, activo: bool}|null
     */
    public function findAccessIdentityById(int $userId): ?array
    {
        $identity = $this->db->table('usuarios u')
            ->select('u.id, u.email, u.nombre, u.apellido, u.estado_solicitud, u.activo, r.nombre AS role')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id')
            ->join('roles r', 'r.id = ru.rol_id')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        if ($identity === null) {
            return null;
        }

        return [
            'id'       => (int) $identity['id'],
            'email'    => $identity['email'],
            'nombre'   => $identity['nombre'],
            'apellido' => $identity['apellido'],
            'role'     => $identity['role'],
            'estado_solicitud' => $identity['estado_solicitud'],
            'activo'   => (bool) $identity['activo'],
        ];
    }

    /**
     * @return list<array{id: int, nombre: string, apellido: string, email: string, telefono: ?string, created_at: ?string}>
     */
    public function findPendingApprovals(): array
    {
        return $this->db->table('usuarios u')
            ->select('u.id, u.nombre, u.apellido, u.email, u.telefono, u.created_at')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id')
            ->join('roles r', 'r.id = ru.rol_id')
            ->where('r.nombre', RolModel::ROLE_PENDING)
            ->where('u.estado_solicitud', self::REQUEST_PENDING)
            ->where('u.activo', 1)
            ->orderBy('u.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array{id: int, nombre: string, apellido: string, email: string, telefono: ?string, role: ?string, estado_solicitud: string, activo: bool, created_at: ?string}>
     */
    public function findAllUserSummaries(): array
    {
        $users = $this->db->table('usuarios u')
            ->select('u.id, u.nombre, u.apellido, u.email, u.telefono, u.estado_solicitud, u.activo, u.created_at, r.nombre AS role')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id', 'left')
            ->join('roles r', 'r.id = ru.rol_id', 'left')
            ->orderBy('u.created_at', 'DESC')
            ->get()
            ->getResultArray();

        foreach ($users as &$user) {
            $user['activo'] = (bool) $user['activo'];
        }
        unset($user);

        return $users;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findManagedUserById(int $userId): ?array
    {
        $user = $this->db->table('usuarios u')
            ->select('u.id, u.nombre, u.apellido, u.email, u.telefono, u.estado_solicitud, u.activo, r.nombre AS role')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id', 'left')
            ->join('roles r', 'r.id = ru.rol_id', 'left')
            ->where('u.id', $userId)
            ->get()
            ->getRowArray();

        if ($user === null) {
            return null;
        }

        $user['activo'] = (bool) $user['activo'];

        return $user;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findPropertiesForUser(int $userId): array
    {
        return $this->db->table('inmuebles i')
            ->select('i.id, i.alias_nombre, i.tipo, i.superficie, i.estado, i.estado_actividad, d.calle, d.numero, d.piso_dpto, d.provincia, d.ciudad')
            ->join('direcciones d', 'd.id = i.direccion_id')
            ->where('i.usuario_id', $userId)
            ->orderBy('i.id', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function findServicesForUserProperties(int $userId): array
    {
        return $this->db->table('inmueble_servicios isv')
            ->select('isv.id AS asociacion_id, isv.inmueble_id, isv.estado AS estado_asociacion, isv.estado_vinculacion, s.nombre AS servicio_nombre, s.categoria AS servicio_categoria, s.proveedor')
            ->join('inmuebles i', 'i.id = isv.inmueble_id')
            ->join('servicios s', 's.id = isv.servicio_id')
            ->where('i.usuario_id', $userId)
            ->orderBy('i.id', 'DESC')
            ->orderBy('s.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function approvePendingUser(int $userId, string $targetRoleName): string
    {
        if (! in_array($targetRoleName, [RolModel::ROLE_OWNER, RolModel::ROLE_ADMINISTRATOR], true)) {
            return self::APPROVAL_INVALID_ROLE;
        }

        $roleModel = new RolModel();
        $pendingRoleId = $roleModel->getIdByName(RolModel::ROLE_PENDING);
        $targetRoleId = $roleModel->getIdByName($targetRoleName);

        if ($pendingRoleId === null || $targetRoleId === null) {
            return self::APPROVAL_ROLE_MISSING;
        }

        if (! $this->db->transBegin()) {
            return self::APPROVAL_FAILED;
        }

        $updated = $this->db->table('rol_usuario')
            ->where('usuario_id', $userId)
            ->where('rol_id', $pendingRoleId)
            ->update(['rol_id' => $targetRoleId]);

        if (! $updated || $this->db->affectedRows() !== 1) {
            $this->db->transRollback();

            return $updated ? self::APPROVAL_NOT_PENDING : self::APPROVAL_FAILED;
        }

        $requestUpdated = $this->db->table('usuarios')
            ->where('id', $userId)
            ->where('estado_solicitud', self::REQUEST_PENDING)
            ->where('activo', 1)
            ->update([
                'estado_solicitud' => self::REQUEST_APPROVED,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

        if (! $requestUpdated) {
            $this->db->transRollback();

            return self::APPROVAL_FAILED;
        }

        if ($this->db->affectedRows() !== 1) {
            $this->db->transRollback();

            return self::APPROVAL_NOT_PENDING;
        }

        if ($this->db->transStatus() === false) {
            $this->db->transRollback();

            return self::APPROVAL_FAILED;
        }

        if (! $this->db->transCommit()) {
            $this->db->transRollback();

            return self::APPROVAL_FAILED;
        }

        return self::APPROVAL_SUCCESS;
    }

    public function rejectPendingUser(int $userId): string
    {
        $updated = $this->db->table('usuarios')
            ->where('id', $userId)
            ->where('estado_solicitud', self::REQUEST_PENDING)
            ->where('activo', 1)
            ->update([
                'estado_solicitud' => self::REQUEST_REJECTED,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

        if (! $updated) {
            return self::APPROVAL_FAILED;
        }

        return $this->db->affectedRows() === 1
            ? self::REQUEST_REJECTION_SUCCESS
            : self::REQUEST_REJECTION_NOT_PENDING;
    }

    public function reopenRejectedRequest(int $userId): string
    {
        $updated = $this->db->table('usuarios')
            ->where('id', $userId)
            ->where('estado_solicitud', self::REQUEST_REJECTED)
            ->where('activo', 1)
            ->update([
                'estado_solicitud' => self::REQUEST_PENDING,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

        if (! $updated) {
            return self::APPROVAL_FAILED;
        }

        return $this->db->affectedRows() === 1
            ? self::REQUEST_REOPEN_SUCCESS
            : self::REQUEST_REOPEN_NOT_REJECTED;
    }

    /**
     * @param array{nombre: string, apellido: string, email: string, telefono: string} $userData
     */
    public function updateManagedUser(int $userId, array $userData, ?string $targetRoleName, int $currentAdminId): string
    {
        $user = $this->findManagedUserById($userId);
        if ($user === null) {
            return self::USER_UPDATE_NOT_FOUND;
        }

        if ($targetRoleName !== null) {
            if (! in_array($targetRoleName, [RolModel::ROLE_OWNER, RolModel::ROLE_ADMINISTRATOR], true)) {
                return self::USER_UPDATE_INVALID_ROLE;
            }

            if (! $user['activo'] || $user['estado_solicitud'] !== self::REQUEST_APPROVED) {
                return self::USER_UPDATE_INVALID_STATE;
            }

            if ($userId === $currentAdminId && $user['role'] !== $targetRoleName) {
                return self::USER_UPDATE_SELF_ROLE_CHANGE;
            }

            if ($user['role'] === RolModel::ROLE_ADMINISTRATOR
                && $targetRoleName !== RolModel::ROLE_ADMINISTRATOR
                && $this->activeAdministratorCount() <= 1) {
                return self::USER_UPDATE_LAST_ADMIN;
            }
        }

        $roleModel = new RolModel();
        $targetRoleId = $targetRoleName === null ? null : $roleModel->getIdByName($targetRoleName);
        $currentRoleId = $user['role'] === null ? null : $roleModel->getIdByName($user['role']);
        if ($targetRoleName !== null && ($targetRoleId === null || $currentRoleId === null)) {
            return self::USER_UPDATE_FAILED;
        }

        if (! $this->db->transBegin()) {
            return self::USER_UPDATE_FAILED;
        }

        $userUpdated = $this->db->table('usuarios')
            ->where('id', $userId)
            ->update($userData + ['updated_at' => date('Y-m-d H:i:s')]);

        if (! $userUpdated || $this->db->transStatus() === false) {
            $this->db->transRollback();

            return self::USER_UPDATE_FAILED;
        }

        if ($targetRoleId !== null && $targetRoleId !== $currentRoleId) {
            $roleUpdated = $this->db->table('rol_usuario')
                ->where('usuario_id', $userId)
                ->where('rol_id', $currentRoleId)
                ->update(['rol_id' => $targetRoleId]);

            if (! $roleUpdated || $this->db->affectedRows() !== 1 || $this->db->transStatus() === false) {
                $this->db->transRollback();

                return self::USER_UPDATE_FAILED;
            }
        }

        if (! $this->db->transCommit()) {
            $this->db->transRollback();

            return self::USER_UPDATE_FAILED;
        }

        return self::USER_UPDATE_SUCCESS;
    }

    public function deactivateUser(int $userId, int $currentAdminId): string
    {
        $user = $this->findManagedUserById($userId);
        if ($user === null || ! $user['activo']) {
            return self::USER_DEACTIVATION_NOT_FOUND;
        }

        if ($userId === $currentAdminId) {
            return self::USER_DEACTIVATION_SELF;
        }

        if ($user['role'] === RolModel::ROLE_ADMINISTRATOR && $this->activeAdministratorCount() <= 1) {
            return self::USER_DEACTIVATION_LAST_ADMIN;
        }

        if (! $this->db->transBegin()) {
            return self::USER_DEACTIVATION_FAILED;
        }

        $deactivated = $this->db->table('usuarios')
            ->where('id', $userId)
            ->where('activo', 1)
            ->update([
                'activo'     => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        if (! $deactivated || $this->db->affectedRows() !== 1 || $this->db->transStatus() === false) {
            $this->db->transRollback();

            return self::USER_DEACTIVATION_FAILED;
        }

        if (! $this->db->transCommit()) {
            $this->db->transRollback();

            return self::USER_DEACTIVATION_FAILED;
        }

        return self::USER_DEACTIVATION_SUCCESS;
    }

    private function activeAdministratorCount(): int
    {
        $count = $this->db->table('usuarios u')
            ->select('COUNT(DISTINCT u.id) AS total', false)
            ->join('rol_usuario ru', 'ru.usuario_id = u.id')
            ->join('roles r', 'r.id = ru.rol_id')
            ->where('r.nombre', RolModel::ROLE_ADMINISTRATOR)
            ->where('u.activo', 1)
            ->get()
            ->getRowArray();

        return (int) ($count['total'] ?? 0);
    }

    /**
     * @return array{status: string, userId?: int}
     */
    public function registerWithDefaultRole(array $userData): array
    {
        if ($this->where('email', $userData['email'])->first() !== null) {
            return ['status' => self::REGISTRATION_EMAIL_EXISTS];
        }
        $userData['estado_solicitud'] = self::REQUEST_PENDING;

        $roleId = (new RolModel())->getIdByName(RolModel::ROLE_PENDING);
        if ($roleId === null) {
            return ['status' => self::REGISTRATION_ROLE_MISSING];
        }

        if (! $this->db->transBegin()) {
            return ['status' => self::REGISTRATION_FAILED];
        }

        $userId = $this->insert($userData);
        $roleAssigned = $userId && (new RolUsuarioModel())->insert([
            'usuario_id' => $userId,
            'rol_id'     => $roleId,
        ]);

        if (! $roleAssigned || $this->db->transStatus() === false) {
            $this->db->transRollback();

            return ['status' => self::REGISTRATION_FAILED];
        }

        if (! $this->db->transCommit()) {
            $this->db->transRollback();

            return ['status' => self::REGISTRATION_FAILED];
        }

        return ['status' => self::REGISTRATION_SUCCESS, 'userId' => (int) $userId];
    }
}