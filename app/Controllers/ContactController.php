<?php

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;

class ContactController
{
    public function index(): void
    {
        View::render('contact', [
            'title' => 'Contact',
        ]);
    }

    public function submit(): void
    {
        csrf_verify();

        $fullName = trim($_POST['full_name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $subject  = trim($_POST['subject'] ?? 'General enquiry');
        $message  = trim($_POST['message'] ?? '');

        if ($fullName === '' || $email === '' || $message === '') {
            flash('error', 'Please fill in your name, email, and message.');
            redirect('/contact');
        }

        $stmt = Database::getInstance()->prepare(
            'INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?,?,?,?,?)'
        );
        $stmt->execute([$fullName, $email, $phone, $subject, $message]);

        flash('success', 'Message sent — the front desk will reply within one business day.');
        redirect('/contact');
    }
}
