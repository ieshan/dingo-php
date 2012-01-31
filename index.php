<?php

require 'dingo.php';
$start = microtime(true);
$app = new Dingo();

# Default route, 'route_map' method allow you to add static route. Parameters are : $route, $callback, $method
$app->route_map('/', function () use ($app) {$app->output_set('Hello World');});

# 404 route
$app->route_map('404',function () use($app) {$app->output_set('No url found');});

# Route with regular expression, Parameters are : $route, $callback, $conditions, $method
$app->route_map_regex('hello/:name', 'hello');

# Route with regular expression and some condition.
$app->route_map_regex('page/:id', 'page', array('id'=>'\d+'));

# Redirection
// Redirect http://<url>/redirect to  http://<url>/redirect_confirm
$app->route_map('redirect', function () use ($app) {$app->redirect('redirect_confirm');});
$app->route_map('redirect_confirm', function() use($app) {$app->output_set('Redirected.');});

# View
$app->route_map('view', function () use ($app) {$app->view('view_1.php', array('str' => 'Hello World'));});

# Route with HTTP method, You can define different controller for different HTTP method for same route.
// GET route. Check http://<url>/method_check via GET request
$app->route_map('method_check', function () use ($app){
	$app->view('view_2.php');
}, array('GET'));

// POST  route. Check http://<url>/method_check via POST request
$app->route_map('method_check', function () use ($app){
	$app->view('view_3.php', array('app' => $app));
}, array('POST'));

// POST  route with 'route_map_regex' method. Check http://<url>/method_check_2 via POST request. If you request via GET request it will show 404 error.
$app->route_map_regex('method_check_2',function () use ($app) {
	$app->output_set('This is a POST request.');
},null, array('POST'));

# Add hook {Available hooks : 'pre_system' , 'pre_route', 'mid_route', 'post_route', 'pre_output', 'post_system'}
// This hook will log execution time.
$app->hook_set('post_system', function () use ($start) {
	$f = fopen('log.txt','a');
	fwrite($f, "\n" . (microtime(true) - $start));
	fclose($f);
});

// This will show execution time if output has '{time}' string
$app->hook_set('pre_output', function () use ($app,$start) {
	$app->output_set(str_replace('{time}',(microtime(true) - $start),$app->output_get()));
});

# The 'run' method runs the application.
$app->run();

// Callback function for 'hello/:name'
function hello($name) {
	$app = Dingo::instance();
	$app->output_set('Hello ' . $name);
}

// Callback function for 'page/:id' route.
function page($id) {
	$app = Dingo::instance();
	$app->output_set('Current page id is ' . $id);
}


?>