<?php

require '../../simpletest/autorun.php';
require '../dingo.php';

class InstanceTest extends UnitTestCase {
	private $app;
	public function setUp() {
		$this->app = new Dingo();
	}

	public function testConfigGet() {
		$this->assertEqual($this->app->config('template_dir'),'./',"Testing if it returns config info if the second parameter of 'config' method is not defined.");
	}
	public function testDefaultConfig() {
		$this->app = new Dingo(array('template_dir'=> '../../'));
		$this->app->config('template_dir','../../');
		$this->assertEqual($this->app->config('template_dir'), '../../', 'Testing if it is possible to set config when initialing class.');
		$this->assertEqual(Dingo::instance()->config('template_dir'),'../../', "Testing previous test with static 'instance' method");
		$this->assertEqual($this->app->config('http_version'), '1.1', 'Testing if other configs are not changed.');
		$this->assertEqual(Dingo::instance()->config('http_version'),'1.1', "Testing previous test with static 'instance' method");
	}

	public function testConfigSet() {
		$this->app->config('hello','world');
		$this->assertEqual($this->app->config('hello'),'world', "Testing if 'config' method can set config if second parameter defined.");
		Dingo::instance()->config('eshan','developer');
		$this->assertEqual(Dingo::instance()->config('eshan'),'developer', "Testing previous test with static 'instance' method");
		$this->app->config('hello','Eshan');
		$this->assertEqual($this->app->config('hello'),'Eshan', "Testing if 'config' method can reset config.");
		Dingo::instance()->config('eshan','coder');
		$this->assertEqual(Dingo::instance()->config('eshan'),'coder', "Testing previous test with static 'instance' method");
		$this->app->config('hello');
		$this->assertEqual($this->app->config('hello'),'Eshan',"Testing that calling 'config' method without second parameter don't change the config value");
		Dingo::instance()->config('eshan');
		$this->assertEqual(Dingo::instance()->config('eshan'),'coder', "Testing previous test with static 'instance' method");
	}
}