<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NativeSession
 *
 * @author atrichkov
 */
/* TODO DBSession */
namespace EF\Sessions;
class NativeSession implements \EF\Sessions\ISession{
    
    public function __construct($name, $lifetime = 3600, $path = null, $domain = null, $secure = null) {
        if (strlen($name)<1){
            $name = '__sess';
        }
        session_name($name);
        session_set_cookie_params($lifetime, $path, $domain, $secure, true);
        session_start();
    }
    
    public function __set($name, $value) {
//        var_Dump($_SESSION);
        $_SESSION[$name] = $value;
    }
    
    public function __get($name){
        return $_SESSION[$name];
    }

    public function destroySession() {
        session_destroy();
    }

    public function getSessionId() {
        return session_id();
    }

    public function saveSession() {
        session_write_close();
    }

}

//$a = new NativeSession($name);
//$a->is_logged=true;

?>
