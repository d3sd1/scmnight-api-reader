<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
umask(0002);
$loader = require __DIR__.'/../app/autoload.php';

if ((isset($_SERVER['HTTP_CLIENT_IP']) || isset($_SERVER['HTTP_X_FORWARDED_FOR']) || !(in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server')) && $_SERVER['HTTP_HOST'] !== 'api.discoexample.dev.scmnight.com')
{
    $env = "prod";
}
else
{
	$env = "dev";
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	Debug::enable();
}
$kernel = new AppKernel($env, $env == "dev" ? true:false);

Request::setTrustedProxies(['~'], Request::HEADER_X_FORWARDED_ALL);
$request = Request::createFromGlobals();

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
