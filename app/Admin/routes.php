<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('/setting', 'HomeController@setting');
    $router->get('/test', 'HomeController@test');

    $router->resource('/hospital_info', 'HospitalInfoController');
    $router->resource('/products', 'ProductController');
    $router->resource('/category', 'CategoryController');
});
