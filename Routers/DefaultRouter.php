<?php
    /**
    * Description of DefaultRouter
    *
    * @author atrichkov
    */
    namespace EF\Routers;
    class DefaultRouter implements \EF\Routers\IRouter{
        
        public function getURI(){
			return substr($_SERVER['REQUEST_URI'], 1);
//		return substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']) + 1);
//            return substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']) + 1);
        }
        
        public function getPost(){
            return $_POST;
        }
        
    }
    