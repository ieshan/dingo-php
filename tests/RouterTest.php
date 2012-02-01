<?php

require '../../simpletest/autorun.php';
require '../dingo.php';

class RouterTest extends UnitTestCase {
	public function get_instance($request_method,$request_uri = '') {
		$_SERVER['REQUEST_METHOD'] = strtoupper($request_method);
		$_SERVER['PATH_INFO'] = trim($request_uri);
		$app = new Dingo(array('send_header'=>false,'send_body'=>false,'run_callback'=>false));
		$app->route_map('404','test_callback_404');
		$app->route_map('/','test_callback_1');
		$app->route_map_regex('hello/:name', 'test_callback_2');
		$app->route_map_regex('page/:id', 'test_callback_3', array('id'=>'\d+'));
		$app->route_map('method_test_1','test_callback_4',array('GET'));
		$app->route_map('method_test_2','test_callback_5',array('GET'));
		$app->route_map('method_test_2','test_callback_6',array('POST'));
		$app->route_map_regex('cool/:name', 'test_callback_7',null,array('GET'));
		$app->route_map_regex('hi/:name', 'test_callback_8',null,array('GET'));
		$app->route_map_regex('hi/:name', 'test_callback_9',null,array('POST'));
		$app->route_map_regex('blog/:id', 'test_callback_10', array('id'=>'\d+'),array('GET'));
		$app->route_map_regex('blog/:id', 'test_callback_11', array('id'=>'\d+'),array('POST'));
		$app->route_map_regex('about', 'test_callback_11',null,array('POST'));
		return $app;
	}

	public function testDefaultRoute() {
		$app = $this->get_instance('GET');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_1','Testing default route');
	}

	public function test404() {
		$app = $this->get_instance('GET','eshan');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing 404 error if not found');
	}

	public function testRegexUrlWithoutCondition() {
		$app = $this->get_instance('GET','hello/world');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_2', 'Testing regular expression based url without condition.');
	}

	public function testRegexWithProperCondition() {
		$app = $this->get_instance('GET','page/12');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_3','Testing regular expression based url with condition.');
	}

	public function testRegexWithWrongCondition() {
		$app = $this->get_instance('GET','page/aa');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing regular expression based url with condition.');
	}

	public function testStaticUrlWithProperHttpMethod() {
		$app = $this->get_instance('GET','method_test_1');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_4', 'Testing static route with proper HTTP method');
	}

	public function testStaticUrlWithWrongHttpMethod() {
		$app = $this->get_instance('POST','method_test_1');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404', 'Testing static route with proper HTTP method');
	}

	public function testRegexUrlWithProperHttpMethod() {
		$app = $this->get_instance('GET','cool/boy');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_7','Testing regex route with proper HTTP method');
	}

	public function testRegexUrlWithWrongHttpMethod() {
		$app = $this->get_instance('POST','cool/boy');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing regex route with wrong HTTP method');
	}

	public function testMultipleStaticUrlCallbackByProperHttpMethod() {
		$app = $this->get_instance('GET','method_test_2');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_5','Testing static route callback for GET method');
		$app = $this->get_instance('POST','method_test_2');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_6','Testing static route callback for POST method');
	}

	public function testMultipleStaticUrlCallbackByWrongHttpMethod() {
		$app = $this->get_instance('PUT','method_test_2');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing static route callback for PUT method');
	}

	public function testMultipleRegexUrlCallbackWithoutConditionByProperHttpMethod() {
		$app = $this->get_instance('GET','hi/eshan');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_8','Testing regex route callback for GET method');
		$app = $this->get_instance('POST','hi/eshan');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_9','Testing static route callback for POST method');
	}

	public function testMultipleRegexUrlCallbackWithoutConditionByWrongHttpMethod() {
		$app = $this->get_instance('PUT','hi/eshan');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing static route callback for PUT method');
	}

	public function testMultipleRegexUrlCallbackWithConditionByProperHttpMethod() {
		$app = $this->get_instance('GET','blog/12');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_10','Testing regex route callback for GET method');
		$app = $this->get_instance('POST','blog/12');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_11','Testing regex route callback for POST method');
		$app = $this->get_instance('GET','blog/a');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing regex route callback for GET method but wrong condition');
		$app = $this->get_instance('POST','blog/a');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing regex route callback for POST method but wrong condition');
	}

	public function testMultipleRegexUrlCallbackWithConditionByWrongHttpMethod() {
		$app = $this->get_instance('PUT','blog/12');
		$app->run();
		$this->assertEqual($app->route_get_callback(),'test_callback_404','Testing regex route callback for PUT method');
	}

	public function testRegexRouteWithParam() {
		$app = $this->get_instance('GET','hi/eshan');
		$app->run();
		$this->assertEqual($app->route_get_params(),array('name'=>'eshan'),'Testing regex route callback parameters');
	}

	public function testRegexRouteWithoutParam() {
		$app = $this->get_instance('POST','about');
		$app->run();
		$this->assertEqual($app->route_get_params(),null,'Testing regex route without parameters');
	}
}