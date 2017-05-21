<?php

/**
 * Description of DefaultController
 *
 * @author atrichkov
 */
namespace EF;
class DefaultController {
    
    /**
     *
     * @var \EF\App 
     */
    public $app;
    /**
     *
     * @var \EF\View
     */
    public $view;
    /**
     *
     * @var \EF\Config 
     */
    public $config;
    /**
     *
     * @var \EF\InputData 
     */
    public $input;
    /**
     *
     * @var \EF\FrontController 
     */
    public $frontController;
    function __construct() {
        $this->app = \EF\App::getInstance();
        $this->view = \EF\View::getInstance();
        $this->config = $this->app->getConfig();
        $this->input = \EF\InputData::getInstance();
        $this->frontController = \EF\FrontController::getInstance();
        $this->common = new \EF\Common();
    }
    
    public function jsonResponse(){
        
    }
    
    public function getParams(){
        return $this->frontController->getParams();
    }
    function normalizeData($key, $type){
        $params = $this->getParams();
        return $this->common->normalize($params[$key], $type);
    }

}

?>
