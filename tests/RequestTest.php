<?php

require '../../simpletest/autorun.php';
require '../dingo.php';

class RequestTest extends UnitTestCase {
	private $app;
	public function setUp() {
		$this->app = new Dingo();
	}

	public function testGet() {
		$_GET['a'] = 'b';
		$_GET['foo'] = '<tag>Text</tag>';
		$_GET['bar'] = array('c'=>'<tag>Text 1</tag>','d'=>array('e'=>'<tag>Text 2</tag>'));
		$xss_cleaned = array('c'=>'&lt;tag&gt;Text 1&lt;/tag&gt;','d'=>array('e'=>'&lt;tag&gt;Text 2&lt;/tag&gt;'));

		$this->assertEqual($this->app->get('x'),false,"Testing if 'get' method returns false for empty key");
		$this->assertEqual($this->app->get('foo'),'<tag>Text</tag>', "Testing if 'get' method returns value");
		$this->assertEqual($this->app->get('foo',true),'&lt;tag&gt;Text&lt;/tag&gt;',"Testing if 'get' returns xss cleaned data");
		$this->assertEqual($this->app->get('bar',true),$xss_cleaned,"Testing if 'get' returns xss cleaned data array");
	}

	public function testPost() {
		$_POST['a'] = 'b';
		$_POST['foo'] = '<tag>Text</tag>';
		$_POST['bar'] = array('c'=>'<tag>Text 1</tag>','d'=>array('e'=>'<tag>Text 2</tag>'));
		$xss_cleaned = array('c'=>'&lt;tag&gt;Text 1&lt;/tag&gt;','d'=>array('e'=>'&lt;tag&gt;Text 2&lt;/tag&gt;'));

		$this->assertEqual($this->app->post('x'),false,"Testing if 'post' method returns false for empty key");
		$this->assertEqual($this->app->post('foo'),'<tag>Text</tag>', "Testing if 'post' method returns value");
		$this->assertEqual($this->app->post('foo',true),'&lt;tag&gt;Text&lt;/tag&gt;',"Testing if 'post' returns xss cleaned data");
		$this->assertEqual($this->app->post('bar',true),$xss_cleaned,"Testing if 'post' returns xss cleaned data array");
	}

	public function testServer() {
		$_SERVER['HTTP_USER_AGENT'] = '<tag>Nokia</tag>';
		$this->assertEqual($this->app->server('x'),false,"Testing if 'server' method returns false for empty key");
		$this->assertEqual($this->app->server('HTTP_USER_AGENT'),'<tag>Nokia</tag>',"Testing if 'server' method returns value");
		$this->assertEqual($this->app->server('HTTP_USER_AGENT',true),'&lt;tag&gt;Nokia&lt;/tag&gt;',"Testing if 'server' returns xss cleaned data");
	}

	public function testCookie() {
		$_COOKIE['a'] = 'b';
		$_COOKIE['foo'] = '<tag>Text</tag>';
		$_COOKIE['bar'] = array('c'=>'<tag>Text 1</tag>','d'=>array('e'=>'<tag>Text 2</tag>'));
		$xss_cleaned = array('c'=>'&lt;tag&gt;Text 1&lt;/tag&gt;','d'=>array('e'=>'&lt;tag&gt;Text 2&lt;/tag&gt;'));

		$this->assertEqual($this->app->cookie('x'),false,"Testing if 'cookie' method returns false for empty key");
		$this->assertEqual($this->app->cookie('foo'),'<tag>Text</tag>', "Testing if 'cookie' method returns value");
		$this->assertEqual($this->app->cookie('foo',true),'&lt;tag&gt;Text&lt;/tag&gt;',"Testing if 'cookie' returns xss cleaned data");
		$this->assertEqual($this->app->cookie('bar',true),$xss_cleaned,"Testing if 'cookie' returns xss cleaned data array");
	}

	public function testAjax() {
		$this->assertEqual($this->app->is_ajax(),false, 'Testing if it is a ajax request');
		$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		$this->assertEqual($this->app->is_ajax(),true, 'Testing if it is a ajax request');
	}
}
