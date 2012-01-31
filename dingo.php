<?php

class Dingo {

	private static $_instance;
	private $_headers = array();
	private $_cookies = array();
	private $_static_routes = array();
	private $_dynamic_routes = array();
	private $_callbacks = array();
	private $_methods = array();
	private $_callback_count = 2;
	private $_request_uri = '';
	private $_base_url = '';
	private $_status = 200;
	private $_response_body = '';
	private $_segments = array();
	private $_configs = array();
	private $_hooks = array();
	private $_params = null;
	private $_routed = false;
	private $_response_msg = array(
		//Informational 1xx
		100 => '100 Continue',
		101 => '101 Switching Protocols',
		//Successful 2xx
		200 => '200 OK',
		201 => '201 Created',
		202 => '202 Accepted',
		203 => '203 Non-Authoritative Information',
		204 => '204 No Content',
		205 => '205 Reset Content',
		206 => '206 Partial Content',
		//Redirection 3xx
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		303 => '303 See Other',
		304 => '304 Not Modified',
		305 => '305 Use Proxy',
		306 => '306 (Unused)',
		307 => '307 Temporary Redirect',
		//Client Error 4xx
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		402 => '402 Payment Required',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		405 => '405 Method Not Allowed',
		406 => '406 Not Acceptable',
		407 => '407 Proxy Authentication Required',
		408 => '408 Request Timeout',
		409 => '409 Conflict',
		410 => '410 Gone',
		411 => '411 Length Required',
		412 => '412 Precondition Failed',
		413 => '413 Request Entity Too Large',
		414 => '414 Request-URI Too Long',
		415 => '415 Unsupported Media Type',
		416 => '416 Requested Range Not Satisfiable',
		417 => '417 Expectation Failed',
		422 => '422 Unprocessable Entity',
		423 => '423 Locked',
		//Server Error 5xx
		500 => '500 Internal Server Error',
		501 => '501 Not Implemented',
		502 => '502 Bad Gateway',
		503 => '503 Service Unavailable',
		504 => '504 Gateway Timeout',
		505 => '505 HTTP Version Not Supported'
	);
	private $_callback;

	/**
	 * Constructor
	 * @param array $config Configuration array
	 */
	public function __construct($config = array()) {
		if (empty(self::$_instance)) {
			self::$_instance = &$this;
		}

		$this->_configs = array_merge(array(
			'http_version' => '1.1',
			'template_dir' => './',
			'cookie_domain' => '',
			'cookie_path' => '/',
			'cookie_secure' => false,
				), $config);

		$_SERVER['REQUEST_METHOD'] = empty($_SERVER['REQUEST_METHOD']) ? 'ALL' : $_SERVER['REQUEST_METHOD'];
		# Getting Base Url
		if (isset($_SERVER['HTTP_HOST'])) {
			$this->_base_url = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
			$this->_base_url .= '://' . $_SERVER['HTTP_HOST'];
		} else {
			$this->_base_url = 'http://localhost';
		}
		$this->_base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
		#Getting Requested Uri
		$this->_request_uri = trim(empty($_SERVER['PATH_INFO']) ? '' : $_SERVER['PATH_INFO'], '/');
		if (empty($this->_request_uri)) {
			$this->_request_uri = '/';
		} else {
			$this->_request_uri = htmlspecialchars($this->_request_uri, ENT_QUOTES, 'UTF-8');
			$this->_request_uri = filter_var($this->_request_uri, FILTER_SANITIZE_STRING);
			$bad = array('$', '(', ')', '%28', '%29', '%00');
			$good = array('&#36;', '&#40;', '&#41;', '&#40;', '&#41;', '');
			$this->_request_uri = str_replace($bad, $good, $this->_request_uri);
			$this->_segments = explode('/', $this->_request_uri);
		}
	}

	/**
	 * @return Dingo Instance
	 */
	public function instance() {
		return self::$_instance;
	}

	/**
	 * Set or get configuration
	 * @param string $key
	 * @param string $value
	 * @return bool
	 */
	public function config($key, $value = null) {
		if (!is_null($value)) {
			$this->_configs[$key] = $value;
		} else {
			return empty($this->_configs[$key]) ? false : $this->_configs[$key];
		}
	}

	/**
	 * Is this a XHR request?
	 * @return bool
	 */
	public function is_ajax() {
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
	}

	/**
	 * Fetch GET parameter(s)
	 * @param string $key
	 * @param bool $xss_clean
	 * @return bool|string
	 */
	public function get($key, $xss_clean = false) {
		return empty($_GET[$key]) ? false : ($xss_clean ? $this->_clean_request($_GET[$key]) : $_GET[$key]);
	}

	/**
	 * Fetch POST parameter(s)
	 * @param string $key
	 * @param bool $xss_clean
	 * @return bool|string
	 */
	public function post($key, $xss_clean = false) {
		return empty($_POST[$key]) ? false : ($xss_clean ? $this->_clean_request($_POST[$key]) : $_POST[$key]);
	}

	/**
	 * Fetch COOKIE parameter(s)
	 * @param string $key
	 * @param bool $xss_clean
	 * @return bool|string
	 */
	public function cookie($key, $xss_clean = false) {
		return empty($_COOKIE[$key]) ? false : ($xss_clean ? $this->_clean_request($_COOKIE[$key]) : $_COOKIE[$key]);
	}

	/**
	 * Fetch SERVER parameter(s)
	 * @param string $key
	 * @param bool $xss_clean
	 * @return bool|string
	 */
	public function server($key, $xss_clean = false) {
		return empty($_SERVER[$key]) ? false : ($xss_clean ? $this->_clean_request($_SERVER[$key]) : $_SERVER[$key]);
	}

	/**
	 * Returns XSS filtered string
	 * @param string $str
	 * @return string
	 */
	public function xss_clean($str) {
		$str = rawurldecode($str);
		$str = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
		$str = htmlentities($str, ENT_QUOTES, 'UTF-8');
		$bad = array('$', '(', ')',);
		$good = array('&#36;', '&#40;', '&#41;',);
		$str = str_replace($bad, $good, $str);
		return $str;
	}

	/**
	 * Sets HTTP header
	 * @param string $key
	 * @param string $value
	 * @return void
	 */
	public function header($key, $value) {
		$this->_headers[$key] = $value;
	}

	/**
	 * Redirects
	 * @param string $url
	 * @param int $status
	 * @throws Stop_dingo
	 */
	public function redirect($url, $status = 302) {
		if (!preg_match('#^https?://#i', $url)) {
			$url = $this->url($url);
		}
		$this->header('Location', $url);
		$this->_status = $status;
		throw new Stop_dingo();
	}

	/**
	 * Sets Cookie
	 * @param string $name
	 * @param string $value
	 * @param int $expire   Cookie expiration time in second. It will delete the cookie if $expire is empty
	 * @param string $domain
	 * @param string $path
	 * @param bool $secure
	 */
	public function set_cookie($name, $value = '', $expire = null, $domain = null, $path = null, $secure = null) {
		$this->_cookies[] = array(
			'name' => $name,
			'value' => $value,
			'expires' => is_numeric($expire) ? (($expire > 0) ? time() + $expire : 0) : (time() - 86500),
			'path' => is_null($path) ? $this->_configs['cookie_path'] : $path,
			'domain' => is_null($domain) ? $this->_configs['cookie_domain'] : $domain,
			'secure' => !is_bool($secure) ? $this->_configs['cookie_secure'] : $secure,
		);
	}

	/**
	 * Sets response status
	 * @param int $status
	 * @return void
	 */
	public function set_status_header($status = 200) {
		$this->_status = $status;
	}

	/**
	 * Sets hook
	 * @param string $key                   Hook name
	 * @param string|function $callback     Callback function
	 * @return void
	 */
	public function hook_set($key, $callback) {
		$this->_hooks[$key][] = $callback;
	}

	/**
	 * Runs hook
	 * @param string $key       Hook name
	 * @param array $params     Hook parameters
	 * @return void
	 */
	public function hook_run($key, $params = null) {
		if (empty($this->_hooks[$key])) {
			return;
		}
		foreach ($this->_hooks[$key] as $hook) {
			if (is_null($params)) {
				call_user_func($hook);
			} else {
				call_user_func_array($hook, $params);
			}
		}
	}

	/**
	 * Returns url segment
	 * @param int $n            Segment number
	 * @param bool $default     If segment not found the default will be returned
	 * @return bool|string
	 */
	public function segment($n, $default = false) {
		return empty($this->_segments[$n]) ? $default : $this->_segments[$n];
	}

	/**
	 * Returns prepared url
	 * @param string $url
	 * @return string
	 */
	public function url($url) {
		return $this->_base_url . trim($url, '/');
	}

	/**
	 * Checks if routing is done or not
	 * @return bool
	 */
	public function routed() {
		return $this->_routed;
	}

	/**
	 * Sets route callback
	 * @param string|function $callback
	 * @param null $params
	 * @return void
	 */
	public function route_set_callback($callback, $params = null) {
		$this->_callback = $callback;
		$this->_params = $params;
		$this->_routed = true;
	}

	/**
	 * Add route
	 * @param string $route                 Static route
	 * @param string|function $callback     Callback function
	 * @param array $methods                HTTP method
	 * @return void
	 */
	public function route_map($route, $callback, $methods = null) {
		$route = empty($route) ? '/' : (($route !== '/') ? trim($route) : '/');
		if (is_null($methods)) {
			$this->_static_routes[$route]['ALL'] = $this->_callback_count;
		}
		else {
			foreach ($methods as $method) {
				$this->_static_routes[$route][$method] = $this->_callback_count;
			}
		}
		$this->_callbacks[$this->_callback_count] = $callback;
		$this->_callback_count++;
	}

	/**
	 * Add route with regular expression
	 * @param string $route                 Regular expression pattern which will be match with requested url
	 * @param string|function $callback     Callback function
	 * @param array $conditions             Additional conditions
	 * @param array $methods                HTTP methods
	 * @return void
	 */
	public function route_map_regex($route, $callback, $conditions = null,$methods = null) {
		$route = trim($route, '/');
		if (is_null($methods)) {
			$this->_dynamic_routes[$route]['ALL'] = array($this->_callback_count,$conditions);
		}
		else {
			foreach ($methods as $method) {
				$this->_dynamic_routes[$route][$method] = array($this->_callback_count,$conditions);
			}
		}
		$this->_callbacks[$this->_callback_count] = $callback;
		$this->_callback_count++;
	}

	/**
	 * Sets output
	 * @param string $output
	 */
	public function output_set($output) {
		$this->_response_body = (string) $output;
	}

	/**
	 * Returns output
	 * @return string
	 */
	public function output_get() {
		return $this->_response_body;
	}

	/**
	 * Appends string into output
	 * @param $output
	 * @return void
	 */
	public function output_append($output) {
		$this->_response_body .= (string) $output;
	}

	/**
	 * Sets view
	 * @param string $template  Template file name
	 * @param array $vars       Variables
	 * @param bool $return      Template should be returned or not
	 * @return void|string
	 */
	public function view($template, $vars = array(), $return = false) {
		extract($vars);
		ob_start();
		require rtrim($this->_configs['template_dir'], '/') . '/' . ltrim($template, '/');
		if ($return) {
			return ob_get_clean();
		} else {
			$this->_response_body .= ob_get_clean();
		}
	}

	/**
	 *
	 * @return void
	 */
	public function run() {
		$this->hook_run('pre_system');
		try {
			$this->hook_run('pre_route', array($this->_request_uri));
			if (!$this->_routed) {
				if (!empty($this->_static_routes[$this->_request_uri])) {
					if (!empty ($this->_static_routes[$this->_request_uri]['ALL'])) {
						$this->route_set_callback($this->_callbacks[$this->_static_routes[$this->_request_uri]['ALL']]);
					}
					elseif (!empty ($this->_static_routes[$this->_request_uri][$_SERVER['REQUEST_METHOD']])) {
						$this->route_set_callback($this->_callbacks[$this->_static_routes[$this->_request_uri][$_SERVER['REQUEST_METHOD']]]);
					}
				}
			}
			$this->hook_run('mid_route', array($this->_request_uri));
			if (!$this->_routed) {
				if (count($this->_dynamic_routes) > 0) {
					foreach ($this->_dynamic_routes as $pattern => $data) {
						if ($this->_regex_url($pattern, $data)) {
							break;
						}
					}
				}
			}
			$this->hook_run('post_route', array($this->_request_uri));
			if (!$this->_routed) {
				$this->set_status_header(404);
				$this->_callback = $this->_callbacks[$this->_static_routes['404']['ALL']];
			}

			if (is_null($this->_params)) {
				call_user_func($this->_callback);
			} else {
				call_user_func_array($this->_callback, $this->_params);
			}
		} catch (Stop_dingo $e) {}

		# Hook 'pre_output' must run before sending header because of 'Content-Length'
		$this->hook_run('pre_output');
		$this->_send_header();
		if (($this->_status < 100 || $this->_status >= 200) && !in_array($this->_status, array(204, 304, 302)) && $_SERVER['REQUEST_METHOD'] !== 'HEAD') {
			echo $this->_response_body;
		}
		$this->hook_run('post_system');
	}

	/**
	 * Checks regex url
	 * @param string $pattern
	 * @param array $arr
	 * @return bool
	 */
	private function _regex_url($pattern, $arr) {
		if (!empty ($arr['ALL'])) {
			$target = $this->_callbacks[$arr['ALL'][0]];
			$conditions = $arr['ALL'][1];
		}
		elseif (!empty ($arr[$_SERVER['REQUEST_METHOD']])) {
			$target = $this->_callbacks[$arr[$_SERVER['REQUEST_METHOD']][0]];
			$conditions = $arr[$_SERVER['REQUEST_METHOD']][1];
		}
		else {
			return false;
		}
		$p_names = array();
		$p_values = array();
		if (preg_match_all('@:([\w]+)@', $pattern, $p_names, PREG_PATTERN_ORDER)) {
			$p_names = $p_names[0];

			if (is_null($conditions)) {
				$url_regex = preg_replace('@:[\w]+@', '([a-zA-Z0-9_\+\-%]+)', $pattern);
			} else {
				$url_regex = preg_replace_callback('@:[\w]+@', function ($matches) use ($conditions) {
					$key = ltrim($matches[0], ':');
					if (array_key_exists($key, $conditions)) {
						return '(' . $conditions[$key] . ')';
					} else {
						return '([a-zA-Z0-9_\+\-%]+)';
					}
				}, $pattern);
			}
			$url_regex .= '/?';
			if (preg_match('@^' . $url_regex . '$@', $this->_request_uri, $p_values)) {
				array_shift($p_values);
				$params = array();
				foreach ($p_names as $index => $val) {
					$params[substr($val, 1)] = urldecode($p_values[$index]);
				}
				if (is_array($target)) {
					foreach ($target as $key => $value) {
						$params[$key] = $value;
					}
				}
				$this->route_set_callback($target, $params);
				return true;
			}
		} else {
			if (preg_match('@^' . $pattern . '$@', $this->_request_uri, $p_values)) {
				$this->route_set_callback($target);
				return true;
			}
		}
		return false;
	}

	/**
	 * Sends HTTP header to browser
	 */
	private function _send_header() {
		if (headers_sent()) {
			return;
		}
		/*if (!in_array($this->_status, array(204, 304, 302))) {
			$this->_headers['Content-Length'] = strlen($this->_response_body);
		}*/
		if (empty($this->_headers['Content-Type'])) {
			$this->_headers['Content-Type'] = 'text/html; charset=utf-8';
		}
		if (substr(PHP_SAPI, 0, 3) === 'cgi') {
			header('Status: ' . $this->_response_msg[$this->_status]);
		} else {
			header('HTTP/' . $this->_configs['http_version'] . ' ' . $this->_response_msg[$this->_status]);
			foreach ($this->_headers as $name => $value) {
				header("{$name}: {$value}");
			}
			foreach ($this->_cookies as $cookie) {
				setcookie($cookie['name'], $cookie['value'], $cookie['expires'], $cookie['path'], $cookie['domain'], $cookie['secure']);
			}
			flush();
		}
	}

	private function _clean_request($data) {
		if (is_array($data)) {
			foreach ($data as $key => $value) {
				if (is_array($value)) {
					$data[$key] = $this->_clean_request($value);
				} else {
					$data[$key] = $this->xss_clean($value);
				}
			}
			return $data;
		} else {
			return $this->xss_clean($data);
		}
	}

}

class Stop_dingo extends Exception {}

?>