<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Mailer;
use App\Core\View;
use App\Models\Customer;
<<<<<<< HEAD
use App\Models\EmailVerification;
=======
>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
use App\Models\PasswordReset;
use App\Models\Wallet;

class AuthController
{
    public function showRegister(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        View::renderAuth('register', ['title' => 'Create Account']);
    }

    public function register(): void
    {
        csrf_verify();

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if ($fullName === '' || $email === '' || $password === '') {
            flash('error', 'Please fill in your name, email, and a password.');
            redirect('/register');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'That email address does not look valid.');
            redirect('/register');
        }
        if (strlen($password) < 8) {
            flash('error', 'Your password should be at least 8 characters.');
            redirect('/register');
        }
        if ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect('/register');
        }
        if (Customer::findByEmail($email)) {
            flash('error', 'An account with that email already exists — try signing in instead.');
            redirect('/login');
        }

        $customerId = Customer::create($fullName, $email, $phone ?: null, password_hash($password, PASSWORD_DEFAULT));
        Wallet::getOrCreateForCustomer($customerId);

<<<<<<< HEAD
        $firstName = explode(' ', $fullName)[0];
        Mailer::sendWelcome($email, $firstName);

        $otp = EmailVerification::generate($customerId);
        Mailer::sendVerificationOtp($email, $firstName, $otp);

        $_SESSION['pending_verification_customer_id'] = $customerId;
        $_SESSION['pending_verification_email'] = $email;

        flash('success', 'Almost there — enter the 6-digit code we just emailed you to verify your account.');
        redirect('/verify-email');
    }

    // ============================================================
    // EMAIL VERIFICATION — OTP FLOW (right after registration)
    // ============================================================

    public function showVerifyEmail(): void
    {
        if (empty($_SESSION['pending_verification_customer_id'])) {
            redirect('/register');
        }
        View::renderAuth('verify-email', [
            'title' => 'Verify Your Email',
            'maskedEmail' => $this->maskEmail($_SESSION['pending_verification_email'] ?? ''),
        ]);
    }

    public function verifyEmail(): void
    {
        csrf_verify();

        $customerId = $_SESSION['pending_verification_customer_id'] ?? null;
        if (!$customerId) {
            redirect('/register');
        }

        $otp = trim($_POST['otp'] ?? '');
        if ($otp === '' || strlen($otp) !== 6) {
            flash('error', 'Enter the 6-digit code from your email.');
            redirect('/verify-email');
        }

        $result = EmailVerification::verify((int) $customerId, $otp);

        if ($result === 'expired') {
            flash('error', 'That code has expired. Request a new one below.');
            redirect('/verify-email');
        }
        if ($result === 'too_many_attempts') {
            flash('error', 'Too many incorrect attempts. Request a new code below.');
            redirect('/verify-email');
        }
        if ($result === 'invalid') {
            flash('error', 'That code is incorrect. Please try again.');
            redirect('/verify-email');
        }

        $customer = Customer::findById((int) $customerId);
        unset($_SESSION['pending_verification_customer_id'], $_SESSION['pending_verification_email']);
        Auth::login($customer);

        flash('success', 'Email verified — welcome to King\'s Cut Saloon! Choose a membership plan whenever you\'re ready.');
        redirect('/membership');
    }

    public function resendVerificationOtp(): void
    {
        csrf_verify();

        $customerId = $_SESSION['pending_verification_customer_id'] ?? null;
        if (!$customerId) {
            redirect('/register');
        }

        $customer = Customer::findById((int) $customerId);
        if ($customer) {
            $otp = EmailVerification::generate($customer['id']);
            $firstName = explode(' ', $customer['full_name'])[0];
            Mailer::sendVerificationOtp($customer['email'], $firstName, $otp);
        }

        flash('success', 'A new code is on its way.');
        redirect('/verify-email');
    }

=======
        $customer = Customer::findById($customerId);
        Auth::login($customer);

        $firstName = explode(' ', $fullName)[0];
        Mailer::sendWelcome($email, $firstName);

        flash('success', 'Account created! Choose a membership plan whenever you\'re ready.');
        redirect('/membership');
    }

>>>>>>> b801f809980fdca60e306a71b1e67d9e42d83bf1
    public function showLogin(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        View::renderAuth('login', ['title' => 'Sign In']);
    }

    public function login(): void
    {
        csrf_verify();

        $email    = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $customer = Customer::findByEmail($email);
        if (!$customer || !password_verify($password, $customer['password_hash'])) {
            flash('error', 'Incorrect email or password.');
            redirect('/login');
        }

        Auth::login($customer);
        redirect('/dashboard');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('/');
    }

    // ============================================================
    // FORGOT PASSWORD — OTP FLOW
    // ============================================================

    public function showForgotPassword(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        View::renderAuth('forgot-password', ['title' => 'Forgot Password']);
    }

    public function sendResetOtp(): void
    {
        csrf_verify();

        $email = trim($_POST['email'] ?? '');
        $customer = Customer::findByEmail($email);

        // Always show the same message, whether or not the email exists,
        // so this endpoint can't be used to discover registered emails.
        if ($customer) {
            $otp = PasswordReset::generate($customer['id']);
            $firstName = explode(' ', $customer['full_name'])[0];
            Mailer::sendOtp($customer['email'], $firstName, $otp);

            $_SESSION['password_reset_customer_id'] = $customer['id'];
            $_SESSION['password_reset_email'] = $customer['email'];
        }

        flash('success', 'If that email is registered, a 6-digit code is on its way. It expires in 10 minutes.');
        redirect('/reset-password');
    }

    public function showResetPassword(): void
    {
        if (empty($_SESSION['password_reset_customer_id'])) {
            flash('error', 'Start by entering your email address.');
            redirect('/forgot-password');
        }
        View::renderAuth('reset-password', [
            'title' => 'Reset Password',
            'maskedEmail' => $this->maskEmail($_SESSION['password_reset_email'] ?? ''),
        ]);
    }

    public function resendResetOtp(): void
    {
        csrf_verify();

        $customerId = $_SESSION['password_reset_customer_id'] ?? null;
        if (!$customerId) {
            redirect('/forgot-password');
        }

        $customer = Customer::findById((int) $customerId);
        if ($customer) {
            $otp = PasswordReset::generate($customer['id']);
            $firstName = explode(' ', $customer['full_name'])[0];
            Mailer::sendOtp($customer['email'], $firstName, $otp);
        }

        flash('success', 'A new code is on its way.');
        redirect('/reset-password');
    }

    public function resetPassword(): void
    {
        csrf_verify();

        $customerId = $_SESSION['password_reset_customer_id'] ?? null;
        if (!$customerId) {
            flash('error', 'Start by entering your email address.');
            redirect('/forgot-password');
        }

        $otp = trim($_POST['otp'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirm'] ?? '');

        if ($otp === '' || strlen($otp) !== 6) {
            flash('error', 'Enter the 6-digit code from your email.');
            redirect('/reset-password');
        }
        if (strlen($password) < 8) {
            flash('error', 'Your new password should be at least 8 characters.');
            redirect('/reset-password');
        }
        if ($password !== $confirm) {
            flash('error', 'Passwords do not match.');
            redirect('/reset-password');
        }

        $result = PasswordReset::verify((int) $customerId, $otp);

        if ($result === 'expired') {
            flash('error', 'That code has expired. Request a new one below.');
            redirect('/reset-password');
        }
        if ($result === 'too_many_attempts') {
            flash('error', 'Too many incorrect attempts. Request a new code below.');
            redirect('/reset-password');
        }
        if ($result === 'invalid') {
            flash('error', 'That code is incorrect. Please try again.');
            redirect('/reset-password');
        }

        Customer::updatePassword((int) $customerId, password_hash($password, PASSWORD_DEFAULT));

        unset($_SESSION['password_reset_customer_id'], $_SESSION['password_reset_email']);

        flash('success', 'Your password has been reset — sign in with your new password.');
        redirect('/login');
    }

    private function maskEmail(string $email): string
    {
        if (!str_contains($email, '@')) {
            return $email;
        }
        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 2);
        return $visible . str_repeat('*', max(1, mb_strlen($local) - 2)) . '@' . $domain;
    }
}
