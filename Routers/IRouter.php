<?php
    /**
     * Description of IRouter
     *
     * @author atrichkov
     */
    namespace EF\Routers;
    interface IRouter{
        public function getURI();
        public function getPost();
    }
   