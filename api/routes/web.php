<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// 公共模块
$router->group(['namespace' => 'Commons'], function () use ($router) {
    $router->get('captcha', 'CaptchaController@index');
    $router->post('login', 'LoginController@index');
    $router->get('login/publickey', 'SecretKeyController@loginPublicKey');

    // 需要token
    $router->group(['middleware' => 'token'], function () use ($router) {
        // 初始化
        $router->get('init', 'InitializeController@index');
        // 个人资料管理
        $router->get('profile', 'ProfileController@index');
        $router->post('profile', 'ProfileController@updateProfile');
        $router->post('password', 'ProfileController@updatePassword');
        // 退出登录
        $router->get('logout', 'LogoutController@index');
        // 获取公钥
        $router->get('logged/publickey', 'SecretKeyController@loggedPublicKey');
        // 上传管理
        $router->post('upload-image', 'UploadController@image');
        $router->post('upload-video', 'UploadController@video');
        $router->post('upload-attachment', 'UploadController@attachment');
    });
});

// token+auth
$router->group(['middleware' => ['token', 'auth']], function () use ($router) {
    // 系统管理模块
    $router->group(['namespace' => 'Systems'], function () use ($router) {
        // 菜单管理
        $router->get('sys/menu', 'MenuController@index');
        $router->get('sys/menu/optional', 'MenuController@optionalMenu');
        $router->post('sys/menu', 'MenuController@add');
        $router->patch('sys/menu', 'MenuController@edit');
        $router->delete('sys/menu/{id:\d+}', 'MenuController@delete');
        // 角色管理
        $router->get('sys/role', 'RoleController@index');
        $router->get('sys/role/permission/{id:\d+}', 'RoleController@initPermission');
        $router->get('sys/role/view/{id:\d+}', 'RoleController@viewChildren');
        $router->post('sys/role', 'RoleController@add');
        $router->post('sys/role/permission', 'RoleController@submitPermission');
        $router->patch('sys/role', 'RoleController@edit');
        $router->delete('sys/role/{id:\d+}', 'RoleController@delete');
        // 管理员管理
        $router->get('sys/admin', 'AdminController@index');
        $router->get('sys/admin/view/{id:\d+}', 'AdminController@viewChildren');
        $router->post('sys/admin', 'AdminController@add');
        $router->patch('sys/admin', 'AdminController@edit');
        $router->patch('sys/admin/password', 'AdminController@resetPassword');
        $router->delete('sys/admin/{id:\d+}', 'AdminController@delete');
    });
    // 日志管理模块
    $router->group(['namespace' => 'Logs'], function () use ($router) {
        $router->get('log/login', 'LoginLogController@index');
        $router->get('log/operation', 'OperationLogController@index');
    });
    // 运维管理模块
    $router->group(['namespace' => 'DevOps'], function () use ($router) {
        $router->get('devops/attachment', 'AttachmentController@index');
    });
});