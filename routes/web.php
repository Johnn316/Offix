<?php

use App\Controllers\HomeController;
use App\Controllers\ContactController;
use App\Controllers\TaskController;
use App\Controllers\ProjectController;
use App\Controllers\ModuleController;
use App\Controllers\LangController;
use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\ProfileController;
use App\Controllers\FileController;
use App\Controllers\SettingsController;
use App\Controllers\ImportController;
use App\Controllers\DocumentController;
use App\Controllers\SignalingController;
use App\Controllers\InlineEditController;

// ── Auth ───────────────────────────────────────────────────────────────────
$router->get( '/login',   AuthController::class, 'loginForm');
$router->post('/login',   AuthController::class, 'login');
$router->get( '/logout',  AuthController::class, 'logout');

// ── File serving (uploads outside webroot) ─────────────────────────────────
$router->get('/file/avatars/{filename}', FileController::class, 'avatar');

// ── Dashboard ──────────────────────────────────────────────────────────────
$router->get('/',          HomeController::class, 'index');
$router->get('/dashboard', HomeController::class, 'index');

// ── Contacts ───────────────────────────────────────────────────────────────
$router->get( '/contacts',              ContactController::class, 'index');
$router->get( '/contacts/import',       ImportController::class,  'form');
$router->post('/contacts/import/run',   ImportController::class,  'run');
$router->get( '/contacts/create',       ContactController::class, 'create');
$router->post('/contacts/store',        ContactController::class, 'store');
$router->get( '/contacts/{id}',         ContactController::class, 'show');
$router->get( '/contacts/{id}/edit',    ContactController::class, 'edit');
$router->post('/contacts/{id}/update',  ContactController::class, 'update');
$router->post('/contacts/{id}/delete',  ContactController::class, 'delete');

// ── Tasks (planner MUST come before {id} routes) ───────────────────────────
$router->get( '/tasks',                 TaskController::class, 'index');
$router->get( '/tasks/create',          TaskController::class, 'create');
$router->get( '/tasks/planner',         TaskController::class, 'planner');
$router->post('/tasks/store',           TaskController::class, 'store');
$router->get( '/tasks/{id}',            TaskController::class, 'show');
$router->get( '/tasks/{id}/edit',       TaskController::class, 'edit');
$router->post('/tasks/{id}/update',     TaskController::class, 'update');
$router->post('/tasks/{id}/delete',     TaskController::class, 'delete');

// ── Projects ───────────────────────────────────────────────────────────────
$router->get( '/projects',              ProjectController::class, 'index');
$router->get( '/projects/create',       ProjectController::class, 'create');
$router->post('/projects/store',        ProjectController::class, 'store');
$router->get( '/projects/{id}',         ProjectController::class, 'show');
$router->get( '/projects/{id}/edit',    ProjectController::class, 'edit');
$router->post('/projects/{id}/update',  ProjectController::class, 'update');
$router->post('/projects/{id}/delete',  ProjectController::class, 'delete');

// ── Users (admin) ─────────────────────────────────────────────────────────
$router->get( '/users',                 UserController::class, 'index');
$router->get( '/users/create',          UserController::class, 'create');
$router->post('/users/store',           UserController::class, 'store');
$router->get( '/users/{id}',            UserController::class, 'show');
$router->get( '/users/{id}/edit',       UserController::class, 'edit');
$router->post('/users/{id}/update',     UserController::class, 'update');
$router->post('/users/{id}/delete',     UserController::class, 'delete');

// ── Profile (own settings) ────────────────────────────────────────────────
$router->get( '/profile',        ProfileController::class, 'edit');
$router->post('/profile/update', ProfileController::class, 'update');

// ── Language switcher ─────────────────────────────────────────────────────
$router->get('/lang/{locale}', LangController::class, 'switch');

// ── Documents ─────────────────────────────────────────────────────────────
$router->get( '/documents',                    DocumentController::class, 'index');
$router->get( '/documents/create',             DocumentController::class, 'create');
$router->post('/documents/create',             DocumentController::class, 'create');
$router->get( '/documents/{id}',               DocumentController::class, 'show');
$router->get( '/documents/{id}/frame',         DocumentController::class, 'frame');
$router->post('/documents/{id}/title',         DocumentController::class, 'updateTitle');
$router->post('/documents/{id}/delete',        DocumentController::class, 'delete');
$router->get( '/documents/{id}/versions',      DocumentController::class, 'versions');
$router->post('/documents/{id}/restore',       DocumentController::class, 'restore');

// ── Signaling API (JSON) ───────────────────────────────────────────────────
$router->post('/api/signal/send',  SignalingController::class, 'send');
$router->get( '/api/signal/poll',  SignalingController::class, 'poll');
$router->post('/api/snapshot',      SignalingController::class, 'snapshot');
$router->get( '/api/doc/state',     SignalingController::class, 'docState');
$router->post('/api/doc/heartbeat', SignalingController::class, 'heartbeat');
$router->post('/api/inline-edit',   InlineEditController::class, 'update');

// ── General settings ──────────────────────────────────────────────────────
$router->get( '/settings',         SettingsController::class, 'index');
$router->post('/settings/update',  SettingsController::class, 'update');

// ── Module settings ────────────────────────────────────────────────────────
$router->get( '/settings/modules',               ModuleController::class, 'index');
$router->post('/settings/modules/{name}/toggle', ModuleController::class, 'toggle');

// ── Optional module routes (loaded only when enabled) ─────────────────────
$moduleRouteDir = ROOT_PATH . '/routes/modules/';
foreach (\App\Core\ModuleManager::enabled() as $moduleName) {
    $file = $moduleRouteDir . $moduleName . '.php';
    if (file_exists($file)) require $file;
}
