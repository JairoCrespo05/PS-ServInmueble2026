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
    public const APPROVAL_FAILED            = 'approval_failed';

    protected $table         = 'usuarios';
    protected $primaryKey    = 'id';
    protected $allowedFields = ['nombre', 'apellido', 'email', 'password', 'telefono', 'direccion_id'];
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

        return [
            'status' => self::LOGIN_SUCCESS,
            'user'   => $identity,
        ];
    }

    /**
     * Load current access data from the database instead of trusting role data in a session.
     *
     * @return array{id: int, email: string, nombre: string, apellido: string, role: string}|null
     */
    public function findAccessIdentityById(int $userId): ?array
    {
        $identity = $this->db->table('usuarios u')
            ->select('u.id, u.email, u.nombre, u.apellido, r.nombre AS role')
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
            ->orderBy('u.created_at', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * @return list<array{id: int, nombre: string, apellido: string, email: string, telefono: ?string, role: ?string, created_at: ?string}>
     */
    public function findAllUserSummaries(): array
    {
        return $this->db->table('usuarios u')
            ->select('u.id, u.nombre, u.apellido, u.email, u.telefono, u.created_at, r.nombre AS role')
            ->join('rol_usuario ru', 'ru.usuario_id = u.id', 'left')
            ->join('roles r', 'r.id = ru.rol_id', 'left')
            ->orderBy('u.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function approvePendingUser(int $userId): string
    {
        $roleModel = new RolModel();
        $pendingRoleId = $roleModel->getIdByName(RolModel::ROLE_PENDING);
        $ownerRoleId = $roleModel->getIdByName(RolModel::ROLE_OWNER);

        if ($pendingRoleId === null || $ownerRoleId === null) {
            return self::APPROVAL_ROLE_MISSING;
        }

        if (! $this->db->transBegin()) {
            return self::APPROVAL_FAILED;
        }

        $updated = $this->db->table('rol_usuario')
            ->where('usuario_id', $userId)
            ->where('rol_id', $pendingRoleId)
            ->update(['rol_id' => $ownerRoleId]);
        $transactionFailed = $this->db->transStatus() === false;

        if (! $updated || $this->db->affectedRows() !== 1 || $transactionFailed) {
            $this->db->transRollback();

            return $transactionFailed || ! $updated
                ? self::APPROVAL_FAILED
                : self::APPROVAL_NOT_PENDING;
        }

        if (! $this->db->transCommit()) {
            $this->db->transRollback();

            return self::APPROVAL_FAILED;
        }

        return self::APPROVAL_SUCCESS;
    }

    /**
     * @return array{status: string, userId?: int}
     */
    public function registerWithDefaultRole(array $userData): array
    {
        if ($this->where('email', $userData['email'])->first() !== null) {
            return ['status' => self::REGISTRATION_EMAIL_EXISTS];
        }

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