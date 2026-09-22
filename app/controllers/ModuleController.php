<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\ModuleManager;

class ModuleController extends Controller
{
    public function __construct()
    {
        $this->requireAdmin();
    }

    public function index(): void
    {
        $this->render('modules.index', [
            'pageTitle' => __('modules.title'),
            'modules'   => ModuleManager::all(),
        ]);
    }

    public function toggle(string $name): void
    {
        $modules = ModuleManager::all();
        $found   = array_filter($modules, fn($m) => $m['name'] === $name);

        if (empty($found)) $this->abort(404, 'Module not found');

        $module    = array_values($found)[0];
        $newState  = !(bool) $module['enabled'];

        ModuleManager::toggle($name, $newState);

        $_SESSION['flash_success'] = ($newState ? 'Enabled' : 'Disabled') . ': ' . $module['label'];
        $this->redirect('/settings/modules');
    }
}
