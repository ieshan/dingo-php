<?php

require '../../simpletest/autorun.php';
require '../dingo.php';

class UrlTest extends UnitTestCase {
	private $app;
	public function setUp() {
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['SCRIPT_NAME'] = '/';
		$_SERVER['PATH_INFO'] = '/test_url/';
		$this->app = new Dingo();
	}

	public function testBase() {
		$this->assertEqual($this->app->url_base(), 'http://localhost/', "Returns base url");
	}

	public function testCurrent() {
		$this->assertEqual($this->app->url_current(),'http://localhost/test_url','Returns current url');
	}

	public function testPreparedUrl() {
		$this->assertEqual($this->app->url('/hello_world/'),'http://localhost/hello_world','Returns prepared url');
		$this->assertEqual($this->app->url('hello_world'),'http://localhost/hello_world','Returns prepared url');
		$this->assertEqual($this->app->url(''),'http://localhost/','Returns prepared url');
	}
}