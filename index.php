<?php

require __DIR__ . '/config.php';

use App\Core\Router;
use App\Controllers\HomeController;
use App\Controllers\ServiceController;
use App\Controllers\MembershipController;
use App\Controllers\ContactController;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\PublicController;
use App\Controllers\ReservationController;
use App\Controllers\WorkController;
use App\Controllers\Admin\AdminAuthController;
use App\Controllers\Admin\AdminDashboardController;
use App\Controllers\Admin\AdminPlanController;
use App\Controllers\Admin\AdminServiceController;
use App\Controllers\Admin\AdminSessionController;
use App\Controllers\Admin\AdminReservationController;
use App\Controllers\Admin\AdminWorkController;
use App\Controllers\Admin\AdminCouponController;
use App\Controllers\Admin\AdminCustomerController;

$router = new Router();

// ---- Public marketing pages ----
$router->get('/', [HomeController::class, 'index']);
$router->get('/services', [ServiceController::class, 'index']);
$router->get('/membership', [MembershipController::class, 'index']);
$router->post('/membership/subscribe', [MembershipController::class, 'subscribe']);
$router->get('/contact', [ContactController::class, 'index']);
$router->post('/contact', [ContactController::class, 'submit']);
$router->get('/reserve', [ReservationController::class, 'showForm']);
$router->post('/reserve', [ReservationController::class, 'submit']);
$router->get('/reserve/check-membership', [ReservationController::class, 'checkMembership']);
$router->get('/reserve/check-coupon', [ReservationController::class, 'checkCoupon']);
$router->get('/work', [WorkController::class, 'index']);

// ---- Auth ----
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/verify-email', [AuthController::class, 'showVerifyEmail']);
$router->post('/verify-email', [AuthController::class, 'verifyEmail']);
$router->post('/verify-email/resend', [AuthController::class, 'resendVerificationOtp']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'showForgotPassword']);
$router->post('/forgot-password/send', [AuthController::class, 'sendResetOtp']);
$router->post('/forgot-password/resend', [AuthController::class, 'resendResetOtp']);
$router->get('/reset-password', [AuthController::class, 'showResetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

// ---- Customer dashboard (auth required, enforced in the controller) ----
$router->get('/dashboard', [DashboardController::class, 'overview']);
$router->get('/dashboard/wallet', [DashboardController::class, 'wallet']);
$router->post('/dashboard/wallet/topup', [DashboardController::class, 'topup']);
$router->get('/dashboard/attendance', [DashboardController::class, 'attendance']);
$router->get('/dashboard/family', [DashboardController::class, 'family']);
$router->post('/dashboard/family/generate', [DashboardController::class, 'generateSecondaryId']);
$router->post('/dashboard/family/revoke/{id}', [DashboardController::class, 'revokeSecondaryId']);
$router->post('/dashboard/family/{id}/share', [DashboardController::class, 'shareSecondaryId']);
$router->post('/dashboard/share/primary/{subscriptionId}', [DashboardController::class, 'sharePrimary']);
$router->get('/dashboard/payments', [DashboardController::class, 'payments']);

// ---- Public share link (no auth) ----
$router->get('/id/{token}', [PublicController::class, 'showId']);

// ---- Admin console ----
$router->get('/admin/login', [AdminAuthController::class, 'showLogin']);
$router->post('/admin/login', [AdminAuthController::class, 'login']);
$router->get('/admin/logout', [AdminAuthController::class, 'logout']);

$router->get('/admin', [AdminDashboardController::class, 'overview']);

$router->get('/admin/plans', [AdminPlanController::class, 'index']);
$router->post('/admin/plans/create', [AdminPlanController::class, 'create']);
$router->post('/admin/plans/{id}/update', [AdminPlanController::class, 'update']);
$router->post('/admin/plans/{id}/delete', [AdminPlanController::class, 'delete']);

$router->get('/admin/services', [AdminServiceController::class, 'index']);
$router->post('/admin/services/create', [AdminServiceController::class, 'createService']);
$router->post('/admin/services/{id}/update', [AdminServiceController::class, 'updateService']);
$router->post('/admin/services/{id}/delete', [AdminServiceController::class, 'deleteService']);
$router->post('/admin/services/categories/create', [AdminServiceController::class, 'createCategory']);
$router->post('/admin/services/categories/{id}/update', [AdminServiceController::class, 'updateCategory']);
$router->post('/admin/services/categories/{id}/delete', [AdminServiceController::class, 'deleteCategory']);

$router->get('/admin/sessions', [AdminSessionController::class, 'index']);
$router->post('/admin/sessions/update', [AdminSessionController::class, 'update']);

$router->get('/admin/coupons', [AdminCouponController::class, 'index']);
$router->post('/admin/coupons/create', [AdminCouponController::class, 'create']);
$router->post('/admin/coupons/{id}/toggle', [AdminCouponController::class, 'toggle']);
$router->post('/admin/coupons/{id}/delete', [AdminCouponController::class, 'delete']);

$router->get('/admin/customers', [AdminCustomerController::class, 'index']);
$router->post('/admin/customers/send-email', [AdminCustomerController::class, 'sendEmail']);
$router->get('/admin/customers/{customerId}/cards', [AdminCustomerController::class, 'cards']);
$router->get('/admin/customers/{customerId}/card/{subscriptionId}', [AdminCustomerController::class, 'showCard']);

$router->get('/admin/reservations', [AdminReservationController::class, 'index']);
$router->post('/admin/reservations/{id}/status', [AdminReservationController::class, 'updateStatus']);

$router->get('/admin/work', [AdminWorkController::class, 'index']);
$router->post('/admin/work/image', [AdminWorkController::class, 'uploadImage']);
$router->post('/admin/work/video', [AdminWorkController::class, 'addVideo']);
$router->post('/admin/work/{id}/delete', [AdminWorkController::class, 'delete']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
