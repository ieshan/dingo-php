<?php

require '../../simpletest/autorun.php';
require '../dingo.php';
class ViewTest extends UnitTestCase {
	private $app;
	public function setUp() {
		$this->app = new Dingo();
	}

	public function testOutputGet() {
		$this->assertEqual($this->app->output_get(),'', "Testing 'output_get' method. It should be empty at the beginning");
		$this->assertEqual(Dingo::instance()->output_get(),'',"Testing previous test with static 'instance' method");
	}

	public function testOutputSet() {
		$this->app->output_set('Hello');
		$this->assertEqual($this->app->output_get(), 'Hello', "The output Should be 'Hello'");
		$this->app->output_set('');
		$this->assertEqual($this->app->output_get(), '', "The output Should be empty");
		# Testing previous test with static 'instance' method.
		Dingo::instance()->output_set('Hello');
		$this->assertEqual(Dingo::instance()->output_get(), 'Hello', "The output Should be 'Hello'");
		Dingo::instance()->output_set('');
		$this->assertEqual(Dingo::instance()->output_get(), '', "The output Should be empty");
	}

	public function testOutputAppend() {
		$this->app->output_set('Hello');
		$this->app->output_append(' World');
		$this->assertEqual($this->app->output_get(), 'Hello World', "The output Should be 'Hello World'");
		$this->app->output_set('');
		# Testing previous test with static 'instance' method.
		Dingo::instance()->output_set('Hello');
		Dingo::instance()->output_append(' World');
		$this->assertEqual(Dingo::instance()->output_get(), 'Hello World', "The output Should be 'Hello World'");
		Dingo::instance()->output_set('');
	}

	public function testView() {
		$this->app->view('view_1.php');
		$this->assertEqual($this->app->output_get(),'Hello',"Testing it the 'view' method can load view");
		$this->app->view('view_2.php');
		$this->assertEqual($this->app->output_get(),'Hello World', "Testing if the 'view' method can append view");
		$this->app->output_set('');
		$this->assertEqual($this->app->output_get(), '', "Testing if 'output_set' method works with view.");
		$this->app->view('view_3.php', array('name'=>'Eshan'));
		$this->assertEqual($this->app->output_get(),'Hello Eshan', "Testing if the 'view' method can set variable");
		$this->app->output_append('. How are you?');
		$this->assertEqual($this->app->output_get(),'Hello Eshan. How are you?', "Testing if the 'output_append' method can append view");
		$this->app->output_set('');
		# Testing previous test with static 'instance' method.
		Dingo::instance()->view('view_1.php');
		$this->assertEqual(Dingo::instance()->output_get(),'Hello',"Testing it the 'view' method can load view");
		Dingo::instance()->view('view_2.php');
		$this->assertEqual(Dingo::instance()->output_get(),'Hello World', "Testing if the 'view' method can append view");
		Dingo::instance()->output_set('');
		$this->assertEqual(Dingo::instance()->output_get(), '', "Testing if 'output_set' method works with view.");
		Dingo::instance()->view('view_3.php', array('name'=>'Eshan'));
		$this->assertEqual(Dingo::instance()->output_get(),'Hello Eshan', "Testing if the 'view' method can set variable");
		Dingo::instance()->output_append('. How are you?');
		$this->assertEqual(Dingo::instance()->output_get(),'Hello Eshan. How are you?', "Testing if the 'output_append' method can append view");
		Dingo::instance()->output_set('');
	}

	public function testViewTemplateDir() {
		$this->app->config('template_dir', '/templates/');
		$this->app->view('view_4.php');
		$this->assertEqual($this->app->output_get(),'Hello World', "Testing if the 'view' method works when 'template_dir changes'");
		$this->app->config('template_dir', 'templates');
		$this->app->output_set('');
		$this->app->view('view_4.php');
		$this->assertEqual($this->app->output_get(),'Hello World', "Testing if the 'view' method works when 'template_dir' has no trailing slash");
		$this->app->config('template_dir','./');
		$this->app->output_set('');
		# Testing previous test with static 'instance' method.
		Dingo::instance()->config('template_dir', '/templates/');
		Dingo::instance()->view('view_4.php');
		$this->assertEqual(Dingo::instance()->output_get(),'Hello World', "Testing if the 'view' method works when 'template_dir changes'");
		Dingo::instance()->config('template_dir', 'templates');
		Dingo::instance()->output_set('');
		Dingo::instance()->view('view_4.php');
		$this->assertEqual(Dingo::instance()->output_get(),'Hello World', "Testing if the 'view' method works when 'template_dir' has no trailing slash");
	}
}

?>