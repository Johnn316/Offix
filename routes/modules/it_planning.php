<?php

use App\Modules\ItPlanning\ItPlanningController;

$router->get('/it_planning',                fn() => (new ItPlanningController())->index());
$router->get('/it_planning/create',         fn() => (new ItPlanningController())->create());
$router->post('/it_planning/store',         fn() => (new ItPlanningController())->store());
$router->get('/it_planning/{id}',           fn($p) => (new ItPlanningController())->show($p['id']));
$router->get('/it_planning/{id}/edit',      fn($p) => (new ItPlanningController())->edit($p['id']));
$router->post('/it_planning/{id}/update',   fn($p) => (new ItPlanningController())->update($p['id']));
$router->post('/it_planning/{id}/delete',   fn($p) => (new ItPlanningController())->delete($p['id']));
