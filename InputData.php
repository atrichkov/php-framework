<?php
/**
 * Description of InputData
 *
 * @author atrichkov
 */
namespace EF;
class InputData {
    private static $_instance = null;
	/**
	*
	* @var get params
	*/
	private $_get = array();
	/**
	*
	* @var post params
	*/
	private $_post = array();
	/**
	*
	* @var cookies params
	*/
	private $_cookies = array();
    
	private function __construct() {
		$this->_cookies = $_COOKIE;
	}

	public function setPost($ar) {
		if (is_array($ar)){
			$this->_post = $ar;
		}
	}

	public function setGet($ar) {
		if (is_array($ar)){
			$this->_get = $ar;
		}
	}

	public function hasGet($id){
		return array_key_exists($id, $this->_get);
	}

	public function hasPost($name){
		return array_key_exists($name, $this->_post);
	}

	public function hasCookies($name){
		return array_key_exists($name, $this->_post);
	}

	public function get($id, $normalize=null, $default=null){
		if($this->hasGet($id)){
			if ($normalize != null){
				return \EF\Common::normalize($this->_get[$id], $normalize);
			}
			return $this->_get($id);
		}
		return $default;
		/* позиция, валидация, дефаулт ако валидацията не мине */
		// $this->get(0, 'trim|string|xss', new);
	}

	public function post($name, $normalize=null, $default=null){
		if($this->hasPost($name)){
			if ($normalize != null){
				return \EF\Common::normalize($this->_post[$name], $normalize);
			}
			return $this->_post($name);
		}
		return $default;
		/* позиция, валидация, дефаулт ако валидацията не мине */
		// $this->get(0, 'trim|string|xss', new);
	}

	public function cookies($name, $normalize=null, $default=null){
		if($this->hasCookies($name)){
			if ($normalize != null){
				return \EF\Common::normalize($this->_cookie($name), $normalize);
			}
			return $this->_cookie($name);
		}
		return $default;
		/* позиция, валидация, дефаулт ако валидацията не мине */
		// $this->get(0, 'trim|string|xss', new);
	}

	/**
	 * 
	 * @return \EF\InputData
	 */
	public static function getInstance(){
		if (self::$_instance == null){
				self::$_instance = new \EF\InputData();
		}
		return self::$_instance;
	}
}
