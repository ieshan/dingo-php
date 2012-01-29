<?php

require 'dingo.php';
$app = new Dingo();
#$app->hook_set('pre_output',function () {Dingo::instance()->output_append("<br />Appended Output");});
$app->hook_set('mid_route',function () use($app) {if (!$app->routed()){$app->route_set_callback('b');}});
$app->route_map('/', function () use ($app) {$app->output_append('Hello World');});
$app->route_map('c', 'c');
$app->route_map_regex('say/:name', function ($name = '') use($app) {$app->output_set("Hello " . $name);});
$app->route_map('404',function () use($app) {$app->output_set('No url found');});
$app->route_map('header','a');
$app->run();

function a() {
	$app = Dingo::instance();
	$app->redirect('/say/name');
}

function b() {
	Dingo::instance()->output_set('Re routed');
}

function c() {
	$app = Dingo::instance();
	$app->view('view_1.php',array('app'=>$app));
}

?>