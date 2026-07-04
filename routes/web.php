<?php
declare(strict_types=1);

/** @var Router $router */

$router->get('/', 'DashboardController@index');

$router->get('/signals', 'SignalController@index');
$router->post('/signals', 'SignalController@store');
$router->post('/signals/{id}/favorite', 'SignalController@favorite');
$router->post('/signals/{id}/score', 'SignalController@score');
$router->post('/signals/{id}/to-project', 'SignalController@toProject');
$router->post('/signals/{id}/delete', 'SignalController@destroy');
$router->post('/signals/{id}/translate', 'SignalController@translate');

$router->get('/projects', 'ProjectController@index');
$router->post('/projects', 'ProjectController@store');
$router->get('/projects/{id}', 'ProjectController@show');
$router->post('/projects/{id}/status', 'ProjectController@updateStatus');
$router->post('/projects/{id}/details', 'ProjectController@updateDetails');
$router->post('/projects/{id}/delete', 'ProjectController@destroy');
$router->post('/projects/{id}/tasks', 'TaskController@store');

$router->post('/tasks/{id}/toggle', 'TaskController@toggle');
$router->post('/tasks/{id}/delete', 'TaskController@destroy');

$router->get('/market', 'MarketController@index');
$router->post('/market/categories', 'MarketController@storeCategory');
$router->post('/market/sectors', 'MarketController@storeSector');
$router->post('/market/sub-sectors', 'MarketController@storeSubSector');
$router->post('/market/companies', 'MarketController@storeCompany');
$router->post('/market/companies/{id}/delete', 'MarketController@destroyCompany');

$router->post('/scan/run', 'ScanController@run');
$router->get('/logs', 'LogController@index');
$router->get('/kaynaklar', 'SourceController@index');
