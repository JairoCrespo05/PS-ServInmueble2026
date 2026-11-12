<?php 

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function login(): string|ResponseInterface
    {
        $postData = $this->request->getPost();
        $email = $postData['email'] ?? '';
        $password = $postData['password'] ?? '';
        $email = is_string($email) ? trim($email) : '';
        $password = is_string($password) ? $password : '';
        $rules = [
            'email'    => 'required|valid_email|max_length[150]',
            'password' => 'required',
        ];
        $messages = [
            'email' => [
                'required'    => 'Ingresá tu correo electrónico.',
                'valid_email' => 'Ingresá un correo electrónico válido.',
                'max_length'  => 'El correo no puede superar los 150 caracteres.',
            ],
            'password' => [
                'required' => 'Ingresá tu contraseña.',
            ],
        ];

        $postData = [
            'email'    => $email,
            'password' => $password,
        ];
        if (! $this->validateData($postData, $rules, $messages)) {
            return $this->loginView(
                implode(' ', $this->validator->getErrors()),
                $email
            );
        }

        $result = (new UserModel())->authenticate($email, $password);
        if ($result['status'] === UserModel::LOGIN_INVALID_CREDENTIALS) {
            return $this->loginView('El correo electrónico o la contraseña no son correctos.', $email);
        }

        if ($result['status'] !== UserModel::LOGIN_SUCCESS) {
            log_message('error', 'No se pudo iniciar sesión porque el usuario no tiene un rol válido.');

            return $this->loginView('No se pudo validar el acceso de esta cuenta. Contactá al administrador.', $email);
        }

        $this->startUserSession($result['user']);

        return redirect()->to(base_url('home'));
    }

    public function singUp(): string|ResponseInterface
    {
        $rules = [
            'first_name'       => 'required|max_length[100]',
            'last_name'        => 'required|max_length[100]',
            'email'            => 'required|valid_email|max_length[150]',
            'phone'            => 'permit_empty|max_length[30]|regex_match[/^[0-9+() -]{8,30}$/]',
            'password'         => 'required|min_length[8]|max_length[72]|regex_match[/[0-9]/]|regex_match[/[!@#$%^&*(),.?":{}<>\x7C]/]',
            'confirm_password' => 'required|matches[password]',
            'terms'            => 'required|in_list[1]',
        ];
        $messages = [
            'first_name' => [
                'required'   => 'El nombre es obligatorio.',
                'max_length' => 'El nombre no puede superar los 100 caracteres.',
            ],
            'last_name' => [
                'required'   => 'El apellido es obligatorio.',
                'max_length' => 'El apellido no puede superar los 100 caracteres.',
            ],
            'email' => [
                'required'    => 'El correo electrónico es obligatorio.',
                'valid_email' => 'Ingresá un correo electrónico válido.',
                'max_length'  => 'El correo no puede superar los 150 caracteres.',
            ],
            'phone' => [
                'max_length'  => 'El teléfono no puede superar los 30 caracteres.',
                'regex_match' => 'Ingresá un teléfono válido (solo números y símbolos telefónicos).',
            ],
            'password' => [
                'required'    => 'La contraseña es obligatoria.',
                'min_length'  => 'La contraseña debe tener al menos 8 caracteres.',
                'max_length'  => 'La contraseña no puede superar los 72 caracteres.',
                'regex_match' => 'La contraseña debe incluir al menos un número y un símbolo especial.',
            ],
            'confirm_password' => [
                'required' => 'Confirmá tu contraseña.',
                'matches'  => 'Las contraseñas no coinciden.',
            ],
            'terms' => [
                'required' => 'Debés aceptar los Términos del Servicio y la Política de Privacidad.',
                'in_list'  => 'Debés aceptar los Términos del Servicio y la Política de Privacidad.',
            ],
        ];

        $postData = $this->request->getPost();
        $oldInput = [];
        foreach (['first_name', 'last_name', 'email', 'phone', 'terms'] as $field) {
            $value = $postData[$field] ?? null;
            if (is_string($value)) {
                $postData[$field] = $field === 'terms' ? $value : trim($value);
                $oldInput[$field] = $postData[$field];
            }
        }

        if (! $this->validateData($postData, $rules, $messages)) {
            return $this->signupView($this->validator->getErrors(), $oldInput);
        }

        $validatedData = $this->validator->getValidated();
        $userData = [
            'nombre'   => $validatedData['first_name'],
            'apellido' => $validatedData['last_name'],
            'email'    => $validatedData['email'],
            'telefono' => $validatedData['phone'] ?? '',
            'password' => password_hash($validatedData['password'], PASSWORD_DEFAULT),
        ];

        $userModel = new UserModel();
        $registrationResult = $userModel->registerWithDefaultRole($userData);
        if ($registrationResult['status'] === UserModel::REGISTRATION_EMAIL_EXISTS) {
            return $this->signupView(
                ['email' => 'Ese correo electrónico ya está registrado.'],
                $oldInput
            );
        }

        if ($registrationResult['status'] === UserModel::REGISTRATION_ROLE_MISSING) {
            return $this->signupView(
                [],
                $oldInput,
                'No se encontró el rol inicial para el registro. Contactá al administrador.'
            );
        }

        if ($registrationResult['status'] !== UserModel::REGISTRATION_SUCCESS) {
            return $this->signupView(
                [],
                $oldInput,
                'No se pudo completar el registro. Verificá los datos e intentá nuevamente.'
            );
        }

        $identity = $userModel->findAccessIdentityById($registrationResult['userId']);
        if ($identity === null) {
            log_message('error', 'Se creó un usuario sin una identidad de acceso válida.');

            return $this->signupView(
                [],
                [],
                'La cuenta se creó, pero no se pudo iniciar la sesión. Iniciá sesión para continuar.'
            );
        }

        $this->startUserSession($identity);

        return redirect()->to(base_url('home'));
    }

    public function logout(): ResponseInterface
    {
        session()->destroy();

        return redirect()->to(base_url());
    }

    /**
     * @param array{id: int, email: string, nombre: string, apellido: string, role: string} $user
     */
    private function startUserSession(array $user): void
    {
        session()->regenerate(true);
        session()->set([
            'user_id'    => $user['id'],
            'user_email' => $user['email'],
            'user_name'  => trim($user['nombre'] . ' ' . $user['apellido']),
        ]);
    }

    private function signupView(array $errors = [], array $oldInput = [], ?string $formError = null): string
    {
        return view('sing_up.php', [
            'errors'    => $errors,
            'oldInput'  => $oldInput,
            'formError' => $formError,
        ]);
    }

    private function loginView(?string $error = null, string $email = ''): string
    {
        return view('login.php', [
            'loginError' => $error,
            'oldEmail'   => $email,
        ]);
    }
}