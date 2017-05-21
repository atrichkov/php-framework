<?php

/**
 * Description of View
 *
 * @author atrichkov
 */
/* Да се изнесе инклудването на самия темплейт в друг обект за да може да се няма достъп до свойствата на основния клас view
 * Засега това е решено с по-шантави имена */

namespace EF;

class View {

	private static $_instance = null;
	private $__viewPath = null;
	private $__viewDir = null;
	private $__cnf = null;
	private $__data = array();
	private $___layoutParts = array();
	private $__layoutData = array();
	private $__extension = '.php';
	public $__allowedExtensions = array('html', 'php');

	private function __construct() {
		$this->__viewPath = \EF\App::getInstance()->getConfig()->app['viewDirectory'];
		$this->__cnf = \EF\App::getInstance()->getConfig()->app['viewConfig'];
		if ($this->__viewPath == null) {
//            $this->__viewPath = realpath('../lab/views/');
		}
	}

	/**
	 *
	 * @var string 
	 */
	public function setViewDirectory($path) {
		$path = trim($path);
		if ($path) {
			$path = realpath($path) . DIRECTORY_SEPARATOR;
			if (is_dir($path) && is_readable($path)) {
				$this->__viewDir = $path;
			} else {
				throw new \Exception('View path missing', 500);
			}
		} else {
			throw new \Exception('View path missing', 500);
		}
	}

	public function display($name, $data = array(), $returnAssString = false, $usetmp = false) {
		$replacementData = $this->__data;
		if (is_array($data)) {
			$this->__data = array_merge($this->__data, $data);
		}
		if (count($this->___layoutParts) > 0) {
			foreach ($this->___layoutParts as $k => $v) {
				$r = $this->_includeFile($v);
				if ($r) {
					$this->__layoutData[$k] = $r;
				}
			}
		}
		if ($returnAssString) {
			return $this->_includeFile($name);
		} else {
			if ($usetmp == true) {
				$t = $this->_includeFile($name);
				$replacementData = array_flip($replacementData);

				//            $c = preg_match('({include.*?})', '/{include="([^"]*)"}/', $tmpl, $matches);
				//            $c = preg_replace('({include.*?})', '/{include="([^"]*)"}/', $this->_includeFile($1), $tmpl);
				//            $c = preg_match_all('({include.*?})', $tmpl);
				//            $tmpl = preg_replace('/{include="([^"]*)"}/', );
				//            preg_match_all('/{include="([^"]*)"}/', $t, $matches);
				$tmpl = $this->parseTmp($t);
				//            $tmpl = preg_replace('/\{include="(.*?)"\}/e', "\$this->parseTemplate('\\1')", $t);
				//            preg_replace('/{include="([^"]*)"}/', '\$this->HtmlPrepare(\'\\1\', \$pObject)', $t);
				//            $tmpl = preg_replace('/{include="([^"]*)"}/', eval('include $1'), $t);
				//            echo $matches[1][0];
				//            var_dump($this->__includedFile);
				//            $tmpl = preg_replace('/{include="([^"]*)"}/', include $this->__viewDir . '' . $matches[1][0] . '', $t);
				//            $tmpl = str_replace($matches[0][0], include $this->__viewDir . '' . $matches[1][0] . '', $t);
				//            $this->_includeFile('test');
				// , '/{include="([^"]*)"}/'
				//          'include' => array('({include.*?})', '/{include="([^"]*)"}/'),
				//            var_dump($tmpl);
				//            $t1 = str_replace(array_values(array_map("self::parseTemplate", $this->__includedFile)), array_keys($this->__includedFile), $tmpl);
				// /{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/
				//        $t2 = preg_replace('{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}', "\$this->loop('\\1')", $tmpl);
				echo str_replace(array_values(array_map("self::parseVariable", $replacementData)), array_keys($replacementData), $tmpl);
			} else {
				echo $this->_includeFile($name);
			}
		}
	}
	
	public function getTemplateParts($name, $data) {		
		if (is_array($data)) {
			$this->__data = array_merge($this->__data, $data);
		}
		if ($this->__viewDir == null) {
			$this->setViewDirectory($this->__viewPath);
		}
		$file = $name;
		$fileParts = explode('.', $name);
		if ($this->__cnf['custom_extension'] && in_array(end($fileParts), $this->__allowedExtensions)) {
			$this->__extension = '.' . end($fileParts);
			array_pop($fileParts);
			$file = implode('.', $fileParts);			
		}
		$___fl = $this->__viewDir . str_replace('.', DIRECTORY_SEPARATOR, $file) . $this->__extension;
		$this->values = $data['values'];
		$data[] = include $___fl;
		
		return $data;
	}

	public function parseTmp($v) {
		$t = preg_replace('/\{include="(.*?)"\}/e', "\$this->parseTemplate('\\1')", $v);
		$result = $this->parseArray($t);
		// preg_replace('/{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/e',
//                "\$this->parseArray('$1')", $v);
//        $result = preg_replace('/\{include="(.*?)"\}/e', "\$this->parseTemplate('\\1')", $v);
//        '({\/loop})', '/{\/loop}/'
		
		return $result;
	}

	public function parseArray($template) {
		preg_match('/{loop="(?<variable>\${0,1}[^"]*)"(?: as (?<key>\$.*?)(?: => (?<value>\$.*?)){0,1}){0,1}}/e', $template, $matches);
		preg_match_all('/{loop[^>]*}(.*?){\/loop}/si', $template, $tmp);
		$arr = substr($matches['variable'], 1);
		preg_match_all('/{(\$.*?)}/', $tmp[1][0], $keys);
		if (is_array($this->$arr)) {
			foreach ($this->$arr as $k => $v) {
				$arrFrom = array('{$' . substr($keys[1][0], 1) . '}', '{$' . substr($keys[1][1], 1) . '}');
				$arrTo = array($k, $v);
				$content .= str_replace($arrFrom, $arrTo, $tmp[1][0]);
			}
		}
		return str_replace($tmp[0][0], $content, $template);
	}

	public static function parseVariable($n) {
		return '{$' . $n . '}';
	}

	public function parseTemplate($v) {
		ob_start();
		include $this->__viewPath . '/' . $v;
		$contents = $this->parseTmp(ob_get_contents());
		ob_end_clean();
		return $contents;
	}

	public function getLayoutData($name) {
		return $this->__layoutData[$name];
	}

	public function setLayoutData($data = array()) {
		if (is_array($data)) {
			$this->__data = array_merge($this->__data, $data);
		}
	}

	private function _includeFile($file) {
		if ($this->__viewDir == null) {
			$this->setViewDirectory($this->__viewPath);
		}
		
		$fileParts = explode('.', $file);
		if ($this->__cnf['custom_extension'] && in_array(end($fileParts), $this->__allowedExtensions)) {
			$this->__extension = '.' . end($fileParts);
			array_pop($fileParts);
			$file = implode('.', $fileParts);			
		}
		$___fl = $this->__viewDir . str_replace('.', DIRECTORY_SEPARATOR, $file) . $this->__extension;
		if (file_exists($___fl) && is_readable($___fl)) {
			ob_start();
			include $___fl;
			return ob_get_clean();
		} else {
			throw new \Exception('View ' . $___fl . ' cannot be included', 500);
		}
	}

	public function appendToLayout($key, $template) {
		if ($key && $template) {
			$this->___layoutParts[$key] = $template;
		} else {
			throw new \Exception('Layaout require valid key and template', 500);
		}
	}

	public function appendDataToLayout($data) {
		return $this->___layoutData = $data;
	}

	public function __set($name, $value) {
		return $this->__data[$name] = $value;
	}

	public function __get($name) {
		return $this->__data[$name];
	}

	/**
	 *
	 * return \EF\View
	 */
	public static function getInstance() {
		if (self::$_instance == null) {
			self::$_instance = new \EF\View();
		}
		return self::$_instance;
	}

}

?>
