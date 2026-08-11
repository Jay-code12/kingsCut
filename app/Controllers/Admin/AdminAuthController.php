<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\Admin;

class AdminAuthController
{
    public function showLogin(): void
    {
        if (AdminAuth::check()) {
            redirect('/admin');
        }
        View::renderAdminAuth('login', ['title' => 'Admin Sign In']);
    }

    public function login(): void
    {
        csrf_verify();

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $admin = Admin::findByEmail($email);
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            flash('error', 'Incorrect email or password.');
            redirect('/admin/login');
        }
        if ($admin['status'] !== 'active') {
            flash('error', 'This admin account has been deactivated.');
            redirect('/admin/login');
        }

        AdminAuth::login($admin);
        redirect('/admin');
    }

    public function logout(): void
    {
        AdminAuth::logout();
        redirect('/admin/login');
    }
}
