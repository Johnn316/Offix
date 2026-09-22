<?php

use App\Modules\Invoicing\InvoiceController;

$router->get('/invoicing',                    fn() => (new InvoiceController())->index());
$router->get('/invoicing/create',             fn() => (new InvoiceController())->create());
$router->post('/invoicing/store',             fn() => (new InvoiceController())->store());
$router->get('/invoicing/{id}',               fn($p) => (new InvoiceController())->show($p['id']));
$router->get('/invoicing/{id}/edit',          fn($p) => (new InvoiceController())->edit($p['id']));
$router->post('/invoicing/{id}/update',       fn($p) => (new InvoiceController())->update($p['id']));
$router->post('/invoicing/{id}/delete',       fn($p) => (new InvoiceController())->delete($p['id']));
$router->get('/invoicing/{id}/print',         fn($p) => (new InvoiceController())->printView($p['id']));
