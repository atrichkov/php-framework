<?php
/**
 * Description of Validation
 *
 * @author atrichkov
 */
namespace EF;
class Validation {
    
	private $_rules = array();
	public $_errors = array();

	public function setRule($rule, $value, $params = null, $name = null, $error = ''){
		$this->_rules[] = array('val' => $value, 'rule' => $rule, 'params' => $params, 'name' => $name, 'error' => $error);
		return $this;
	}

	public function validate(){
		if (count($this->_rules) > 0){
			foreach ($this->_rules as $v){
				if (!$this->$v['rule']($v['val'], $v['params'])){ // Тук директно викаме съответно зададеното правило с параметри
					if ($v['name']){
						$this->_errors[$v['name']] = $v['error'];
					} else {
						$this->_errors[] = $v['rule'];
					}
				}
			}
		}
		return (bool) !count($this->_errors);
	}

	public function getЕrrors() {
		return $this->_errors;
	}

	public function __call($a, $b) {
		throw new \Exception('Invalid validation rule', 500);
	}

	public static function required($val) {
		if (is_array($val)) {
			return !empty($val);
		} else {
			return $val != '';
		}
	}
	
	/**
	 * compare two values
	 * 
	 * @var string
	 * @var string
	 */
	public static function matches($val1, $val2) {
		return $val1 == $val2;
	}
	
	/**
	 * compare two values strict mode
	 * 
	 * @var string
	 * @var string
	 */
	public static function matchesStrict($val1, $val2) {
		return $val1 === $val2;
	}

	public static function different($val1, $val2) {
		return $val1 != $val2;
	}

	public static function differentStrict($val1, $val2) {
		return $val1 !== $val2;
	}

	public static function minlength($val1, $val2) {
		return (mb_strlen($val1) >= $val2);
	}

	public static function maxlength($val1, $val2) {
		return (mb_strlen($val1) <= $val2);
	}

	public static function exactlength($val1, $val2) {
		return (mb_strlen($val1) == $val2);
	}

	public static function gt($val1, $val2) {
		return ($val1 > $val2);
	}

	public static function lt($val1, $val2) {
		return ($val1 < $val2);
	}

	public static function alpha($val1) {
		return (bool) preg_match('/^([a-z])+$/i', $val1);
	}

	public static function alphanum($val1) {
		return (bool) preg_match('/^([a-z0-9])+$/i', $val1);
	}

	public static function alphanumdash($val1) {
		return (bool) preg_match('/^([-a-z0-9_-])+$/i', $val1);
	}

	public static function numeric($val1) {
		return is_numeric($val1);
	}

	/**
	 * validate email
	 * 
	 * @var string
	 * @return boolean
	 */
	public static function email($val1) {
		return filter_var($val1, FILTER_VALIDATE_EMAIL) !== false;
	}

	/**
	 * validate emails
	 * 
	 * @var array
	 * @return boolean
	 */
	public static function emails($val1) {
		if (is_array($val1)) {
			foreach ($val1 as $v) {
				if (!self::email($val1)) {
					return false;
				}
			}
		} else {
			return false;
		}
		return true;
	}

	public static function url($val1) {
		return filter_var($val1, FILTER_VALIDATE_URL) !== false;
	}

	public static function ip($val1) {
		return filter_var($val1, FILTER_VALIDATE_IP) !== false;
	}

	public static function regexp($val1, $val2) {
		return (bool) preg_match($val2, $val1);
	}

	public static function custom($val1, $val2) {
		if ($val2 instanceof \Closure) {
			return (boolean) call_user_func($val2, $val1);
		} else {
			throw new \Exception('Invalid validation function', 500);
		}
	}
}

?>
