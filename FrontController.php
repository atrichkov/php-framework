<?php

/**
 * Description of FrontController
 *
 * @author atrichkov
 */

namespace EF;

class FrontController {

	private static $_instance = null;
	private $ns = null;
	private $controller = null;
	private $method = null;
	private $router = null;
	private $params = null;

	public function __construct() {
		
	}

	/* Задължително файловете на контролерите задължително с главна буква като файловете всичко останало с малки букви!!! */

	public function getRouter() {
		return $this->router;
	}

	// Задължаваме имплементацията на IRouter
	public function setRouter(\EF\Routers\IRouter $router) {
		$this->router = $router;
	}

	public function dispatch() {
		if ($this->router == null) {
			throw new \Exception('No valid router found', 500);
		}
		$_uri = $this->router->getURI();
		$routes = \EF\App::getInstance()->getConfig()->routes;
		$_rc = null;
//		var_dump($_uri);
//		echo '<br />';
		if (is_array($routes) && count($routes) > 0) {
			foreach ($routes as $k => $v) {
//				echo $k;
//				echo '<br />';

				if (stripos($_uri, $k) === 0 && ($_uri == $k || stripos($_uri, $k . '/') === 0) && $v['namespace']) {
					$this->ns = $v['namespace'];
					$_uri = substr($_uri, strlen($k) + 1);
					$_rc = $v;
					break;
				}
			}
		} else {
			throw new \Exception('Default route missing', 500);
		}
		if ($this->ns == null && $routes['*']['namespace']) {
			$this->ns = $routes['*']['namespace'];
//            $_rc = $routes['*']['controllers'];
		} else if ($this->ns == null && !$routes['*']['namespace']) {
			throw new \Exception('Default route missing', 500);
		}
		$input = \EF\InputData::getInstance();
		$_params = explode('/', $_uri);
		if ($_params[0]) {
			$ctrlPrefix = \EF\App::getInstance()->getConfig()->app['controllerPrefix'];
			$this->controller = $_params[0] . $ctrlPrefix;
			if ($_params[1]) {
				$this->method = strtolower($_params[1]);
				unset($_params[0], $_params[1]);
				$input->setGet(array_values($_params));
				$this->params = array_values($_params);
			} else {
				$this->method = $this->getDefaultMethod();
			}
		} else {
			$this->controller = $this->getDefaultController();
			$this->method = $this->getDefaultMethod();
		}
		if (is_array($_rc) && $_rc['controllers']) {
			if ($_rc['controllers'][$this->controller]['methods'][$this->method]) {
				$this->method = strtolower($_rc['controllers'][$this->controller]['methods'][$this->method]);
			}
			// $_rc['controllers'][$this->controller]
			if (isset($_rc['controllers'][$this->controller]['to'])) {
				$this->controller = strtolower($_rc['controllers'][$this->controller]['to']);
			}
		}

		$input->setPost($this->router->getPost());
		$f = $this->ns . '\\' . ucfirst($this->controller);
		$newController = new $f();
		$newController->{$this->method}();
	}

	function getDefaultController() {
		$controller = \EF\App::getInstance()->getConfig()->app['default_controller'];
		if ($controller) {
			return strtolower($controller);
		}
		return 'index';
	}

	function getDefaultMethod() {
		$method = \EF\App::getInstance()->getConfig()->app['default_method'];
		if ($method) {
			return strtolower($method);
		}
		return 'index';
	}

	function getParams() {
		return $this->params;
	}

	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new \EF\FrontController();
		}
		return self::$_instance;
	}

}
