<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (!empty($_SESSION['user_id'])) $this->redirect('/');

        $this->render('auth.login', [
            'pageTitle' => 'Login',
            'error'     => $_SESSION['login_error'] ?? null,
        ], 'auth_layout');

        unset($_SESSION['login_error']);
    }

    public function login(): void
    {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        $model = new User();
        $user  = $model->findByEmail($email);

        if (!$user || !$user['is_active'] || !password_verify($password, $user['password'])) {
            $_SESSION['login_error'] = 'Invalid email or password.';
            $this->redirect('/login');
        }

        // New session ID on privilege change, so a session ID an attacker
        // planted before login is worthless afterwards (session fixation).
        session_regenerate_id(true);

        // A token handed out before authentication must not stay valid after it.
        csrf_rotate();

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_avatar'] = $user['avatar'];
        $_SESSION['locale']    = $user['language'];

        Lang::setLocale($user['language']);
        $model->updateLastLogin($user['id']);

        $this->redirect('/');
    }

    public function logout(): void
    {
        // Clear the data, expire the cookie in the browser, then destroy the
        // server-side session - destroying alone leaves both of the first two.
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => $p['httponly'],
                'samesite' => $p['samesite'] ?? 'Lax',
            ]);
        }

        session_destroy();

        // Fresh, empty session so the login page can issue a new CSRF token.
        session_start();
        session_regenerate_id(true);

        $this->redirect('/login');
    }
}
