<?php

namespace App\Controllers\Admin;

use App\Core\AdminAuth;
use App\Core\View;
use App\Models\ServiceCatalog;

class AdminServiceController
{
    public function index(): void
    {
        AdminAuth::requireSuperAdmin();

        $categories = ServiceCatalog::allGrouped();

        View::renderAdmin('services', [
            'title' => 'Services & Categories',
            'admin' => AdminAuth::user(),
            'activeNav' => 'services',
            'categories' => $categories,
        ]);
    }

    // ---- Categories ----

    public function createCategory(): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', 'Give the category a name.');
            redirect('/admin/services');
        }

        ServiceCatalog::createCategory($name, (int) ($_POST['sort_order'] ?? 0));
        flash('success', 'Category "' . $name . '" added.');
        redirect('/admin/services');
    }

    public function updateCategory(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', 'Give the category a name.');
            redirect('/admin/services');
        }

        ServiceCatalog::updateCategory((int) $id, $name, (int) ($_POST['sort_order'] ?? 0));
        flash('success', 'Category updated.');
        redirect('/admin/services');
    }

    public function deleteCategory(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        if (ServiceCatalog::deleteCategory((int) $id)) {
            flash('success', 'Category removed.');
        } else {
            flash('error', 'Move or delete its services first — a category with services in it can\'t be removed.');
        }
        redirect('/admin/services');
    }

    // ---- Services ----

    public function createService(): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $name = trim($_POST['name'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $price = trim((string) ($_POST['standard_price'] ?? ''));

        if ($name === '' || $categoryId <= 0 || $price === '') {
            flash('error', 'A service needs a name, category, and price.');
            redirect('/admin/services');
        }

        $compareAt = trim((string) ($_POST['compare_at_price'] ?? ''));
        if ($compareAt !== '' && (float) $compareAt <= (float) $price) {
            $compareAt = ''; // ignore a strike price that isn't actually higher
        }

        ServiceCatalog::createService([
            'category_id' => $categoryId,
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'duration_minutes' => $_POST['duration_minutes'] ?? 30,
            'standard_price' => $price,
            'compare_at_price' => $compareAt,
            'sort_order' => $_POST['sort_order'] ?? 0,
        ]);

        flash('success', 'Service "' . $name . '" added.');
        redirect('/admin/services');
    }

    public function updateService(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        $name = trim($_POST['name'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $price = trim((string) ($_POST['standard_price'] ?? ''));

        if ($name === '' || $categoryId <= 0 || $price === '') {
            flash('error', 'A service needs a name, category, and price.');
            redirect('/admin/services');
        }

        $compareAt = trim((string) ($_POST['compare_at_price'] ?? ''));
        if ($compareAt !== '' && (float) $compareAt <= (float) $price) {
            $compareAt = '';
        }

        ServiceCatalog::updateService((int) $id, [
            'category_id' => $categoryId,
            'name' => $name,
            'description' => trim($_POST['description'] ?? ''),
            'duration_minutes' => $_POST['duration_minutes'] ?? 30,
            'standard_price' => $price,
            'compare_at_price' => $compareAt,
            'sort_order' => $_POST['sort_order'] ?? 0,
        ]);

        flash('success', 'Service updated — changes are live on the Services page now.');
        redirect('/admin/services');
    }

    public function deleteService(string $id): void
    {
        AdminAuth::requireSuperAdmin();
        csrf_verify();

        ServiceCatalog::deleteService((int) $id);
        flash('success', 'Service removed.');
        redirect('/admin/services');
    }
}
