<?php
    /**
    * Setup base config and run application
    *
    * @author atrichkov
    */
    namespace EF;
//    include_once 'Loader.php';
    class App{
        private static $_instance = null;
        private $_frontController = null;
        private $_config = null;
        private $router = null;
        private $_dbConnections = array();
        private $_session = null;
        
        private function __construct() {
		/* Регистрира namespace 
			1 - име на namespace-а
			2 - директория на където да води съответния namespace 
		*/
		set_exception_handler(array($this, '_exceptionHandler'));
//		\EF\Loader::registerNamespace('EF', dirname(__FILE__) . DIRECTORY_SEPARATOR);
//		\EF\Loader::registerAutoLoad();
		$this->_config= \EF\Config::getInstance();
        }
        
        public function setConfigFolder($path){
            $this->_config->setConfigFolder($path);
        }
        public function getConfigFolder(){
            return $this->_configFolder;
        }
        
        public function getRouter() {
            return $this->router;
        }
		
	/**
	* Set router from \EF\Routers
	*
	* @var \EF\Routers
	*/
        public function setRouter($router) {
            $this->router = $router;
        }
        
        public function getConfig(){
            return $this->_config;
        }
        
        public function run(){
            if ($this->_config->getConfigFolder() == null){
                $this->setConfigFolder('../lab/config'); // fix this path
            } else {
//                throw new \Exception('Config directory read error' . $_configFolder);
            }
            $this->_frontController= \EF\FrontController::getInstance();
            if ($this->router instanceof \EF\Routers\IRouter){ // в случай, че се предаде обект който имплементира IRouter
                $this->_frontController->setRouter($this->router);
            } else if ($this->router == 'JsonRPCRouter'){
                $this->_frontController->setRouter(new \EF\Routers\JsonRPCRouter());
            } else if($this->router == 'CLIRouter'){
                // TODO create CLIRouter
                $this->_frontController->setRouter(new \EF\Routers\DefaultRouter());
            } else {
                $this->_frontController->setRouter(new \EF\Routers\DefaultRouter());
            }
            $_sess = $this->_config->app['session'];
            if ($_sess['autostart']){
                if ($_sess['type'] == 'native'){
                    $_s = new \EF\Sessions\NativeSession($_sess['name'], $_sess['lifetime'], $_sess['path'], $_sess['domain'], $_sess['secure']);
                }
                $this->setSession($_s);
            }
            
            $this->_frontController->dispatch();
        }
        
        public function setSession(\EF\Sessions\ISession $session) {
            $this->_session = $session;
        }
        
        public function getSession() {
            return $this->_session;
        }
        
        public function getDBConnection($connection = 'default'){
            if (!$connection){
                throw new \Exception('No connection identifiler provide', 500);
            }
            if ($this->_dbConnections[$connection]){
                return $this->_dbConnections[$connection];
            }
            $_cnf = $this->getConfig()->database;
            if (!$_cnf[$connection]){
                throw new \Exception('No connection identifiler provide', 500);
            }
            $dbh = new \PDO($_cnf[$connection]['connection_uri'], $_cnf[$connection]['username'], $_cnf[$connection]['password'], $_cnf[$connection]['pdo_options']);
            $this->_dbConnections[$connection] = $dbh;
            return $dbh;
        }
        
        /**
        *
        * return \EF\App
        */
        public static function getInstance(){
            if (self::$_instance == null){
                    self::$_instance = new \EF\App();
            }
            return self::$_instance;
        }
        
        public function _exceptionHandler(\Exception $ex) {        
            if ($this->_config && $this->_config->app['displayExceptions'] == true) {
                echo '<pre>' . print_r($ex, true) . '</pre>';
            } else {
                $this->displayError($ex->getCode());
            }
        }
        
        public function displayError($error){
            try{
                $view = \EF\View::getInstance();
                $view->display('errors', $error);
            } catch (\Exception $exc){
                \EF\Common::headerStatus($error);
                echo '<h1>' . $error . '</h1>';
                exit;
            }
        }
    }