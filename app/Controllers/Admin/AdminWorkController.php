<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\WorkItem;

class AdminWorkController
{
    private const ALLOWED_MIME = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    private const MAX_BYTES = 5 * 1024 * 1024; // 5MB

    public function index(): void
    {
        AdminAuth::requireLogin();

        View::renderAdmin('work', [
            'title' => 'Our Work',
            'admin' => AdminAuth::user(),
            'activeNav' => 'work',
            'items' => WorkItem::allForAdmin(),
        ]);
    }

    public function uploadImage(): void
    {
        AdminAuth::requireLogin();
        csrf_verify();

        $title = trim($_POST['title'] ?? '');

        if (empty($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            flash('error', 'Choose an image to upload.');
            redirect('/admin/work');
        }
        $file = $_FILES['image'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            flash('error', 'Upload failed — please try again.');
            redirect('/admin/work');
        }
        if ($file['size'] > self::MAX_BYTES) {
            flash('error', 'That image is too large — please keep it under 5MB.');
            redirect('/admin/work');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset(self::ALLOWED_MIME[$mime])) {
            flash('error', 'Please upload a JPG, PNG, WEBP, or GIF image.');
            redirect('/admin/work');
        }

        $ext = self::ALLOWED_MIME[$mime];
        $filename = 'work-' . bin2hex(random_bytes(8)) . '.' . $ext;
        $destDir = __DIR__ . '/../../../assets/uploads/work';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $destPath = $destDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            flash('error', 'Could not save the uploaded file — check folder permissions on assets/uploads/work.');
            redirect('/admin/work');
        }

        WorkItem::createImage($title, 'uploads/work/' . $filename, (int) ($_POST['sort_order'] ?? 0));

        flash('success', 'Image added to the Our Work gallery.');
        redirect('/admin/work');
    }

    public function addVideo(): void
    {
        AdminAuth::requireLogin();
        csrf_verify();

        $title = trim($_POST['title'] ?? '');
        $url = trim($_POST['youtube_url'] ?? '');

        if ($url === '') {
            flash('error', 'Paste a YouTube link.');
            redirect('/admin/work');
        }

        $videoId = WorkItem::extractYoutubeId($url);
        if (!$videoId) {
            flash('error', 'That doesn\'t look like a YouTube link — try copying it directly from the address bar or the Share button.');
            redirect('/admin/work');
        }

        WorkItem::createVideo($title, $url, $videoId, (int) ($_POST['sort_order'] ?? 0));

        flash('success', 'Video added to the Our Work gallery.');
        redirect('/admin/work');
    }

    public function delete(string $id): void
    {
        AdminAuth::requireLogin();
        csrf_verify();

        $item = WorkItem::find((int) $id);
        if ($item && $item['type'] === 'image' && $item['image_path']) {
            $path = __DIR__ . '/../../../assets/' . $item['image_path'];
            if (is_file($path)) {
                @unlink($path);
            }
        }

        WorkItem::delete((int) $id);

        flash('success', 'Removed from the Our Work gallery.');
        redirect('/admin/work');
    }
}
