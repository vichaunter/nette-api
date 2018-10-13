<?php
/**
 * Created by PhpStorm.
 * User: vicha
 * Date: 24/08/2018
 * Time: 10:29
 */

namespace VicHaunter\Nette\Api;

use Nette\Application\IPresenter;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Security\AuthenticationException;

class ApiPresenter implements IPresenter {
    
    /** @var $request Request */
    protected $request;
    
    /** @var Container @inject */
    public $container;
    
    protected $parameters;
    
    public function getContainerParameter( $name ) {
        return isset($this->container->getParameters()[ $name ]) ? $this->container->getParameters()[ $name ] : null;
    }
    
    /**
     * Get remote response data and validate it against defined rules in array
     *
     * @param $validation
     *
     * @return mixed
     */
    protected function getDataRequest() {
        $parameters = array_merge($this->request->getPost(), $this->request->getParameters());
        if (!$this->getContainerParameter('token_secret')
            || !isset($parameters['token'])
            || $parameters['token'] != $this->getContainerParameter('token_secret')) {
            throw new AuthenticationException("Forbidden");
        }
        unset($parameters['token']);
        
        return $parameters;
    }
    
    /**
     * @return IResponse
     */
    function run( Request $request ) {
        $this->request = $request;
        $this->parameters = $this->getDataRequest();
        $action = 'action'.ucfirst($request->getParameter('action'));
        $r = $this->$action();
        return new JsonResponse($r);
    }
    
}