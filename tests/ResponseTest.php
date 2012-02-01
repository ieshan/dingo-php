<?php

require '../../simpletest/autorun.php';
require '../dingo.php';

class ResponseTest extends UnitTestCase {
	private $app;
	public function get_instance($request_uri) {
		$_SERVER['PATH_INFO'] = trim($request_uri);
		$app = new Dingo(array('send_header'=>false,'send_body'=>false,'run_callback'=>false));
		$app->route_map('/','test_callback_1');
		$app->route_map('404','test_callback_404');
		$app->route_map_regex('hello/:name', 'test_callback_2');
		return $app;
	}

	public function testIsOkStatus() {
		$app = $this->get_instance('');
		$app->run();
		$this->assertEqual($app->status(),200,"Testing '200 ok' status.");
	}

	public function test404Status() {
		$app = $this->get_instance('aadks');
		$app->run();
		$this->assertEqual($app->status(),404,"Testing '404 not found' status.");
	}

	public function testHeaderGetSet() {
		$app = $this->get_instance('');
		$app->run();
		$app->header('Content-Type','text/html');
		$this->assertEqual($app->header('Content-Type'),'text/html',"Testing if 'header' method can return header info is second parameter is not defined");
	}
}