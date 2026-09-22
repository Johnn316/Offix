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
        session_destroy();
        session_start();
        $this->redirect('/login');
    }
}
