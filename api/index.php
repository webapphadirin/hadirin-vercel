<?php
use Illuminate\Http\Request;
define('LARAVEL_START', microtime(true));
$storage='/tmp/hadirin';
foreach (['framework/views','framework/sessions','framework/cache','logs','bootstrap'] as $dir) {
    if (!is_dir($storage.'/'.$dir)) mkdir($storage.'/'.$dir,0700,true);
}
foreach (['VIEW_COMPILED_PATH'=>$storage.'/framework/views','APP_SERVICES_CACHE'=>$storage.'/bootstrap/services.php','APP_PACKAGES_CACHE'=>$storage.'/bootstrap/packages.php'] as $key=>$value) {
    putenv($key.'='.$value); $_ENV[$key]=$value; $_SERVER[$key]=$value;
}
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->useStoragePath($storage);
$app->handleRequest(Request::capture());
