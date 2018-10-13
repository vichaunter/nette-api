<?php
/**
 * Created by PhpStorm.
 * User: Antonin Sajboch
 * Date: 4/2/18
 * Time: 3:56 PM
 */

namespace VicHaunter\Nette\Api\Bridges\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\Loaders\RobotLoader;
use VicHaunter\Nette\Api\BaseApiModel;

class ApiExtension extends CompilerExtension {
    
    public $defaults = [
        'db'       => [],
        'profiler' => true,
        'scanDirs' => null,
        'logFile'  => null,
    ];
    
    /**
     * Returns extension configuration.
     * @return array
     */
    public function getConfig() {
        $container = $this->getContainerBuilder();
        $this->defaults['scanDirs'] = $container->expand('%appDir%');
        
        return parent::getConfig($this->defaults);
    }
    
    public function loadConfiguration() {
        $container = $this->getContainerBuilder();
        $config = $this->getConfig();
        $index = 1;
        foreach ($this->findApiModels($config) as $apiModel) {
            $container->addDefinition($this->
            prefix('apiModel.'.$index++))
                      ->setClass($apiModel)
                      ->setInject(true)
                      ->setAutowired(true);
        }
    }
    
    private function findApiModels( $config ) {
        $classes = [];
        if ($config['scanDirs']) {
            $robot = new RobotLoader();
            //TODO: set storage FileStorage
//            $robot->setCacheStorage(new DevNullStorage());
            $robot->addDirectory($config['scanDirs']);
            $robot->acceptFiles = '*.php';
            $robot->rebuild();
            $classes = array_keys($robot->getIndexedClasses());
        }
        $apiModels = [];
        foreach (array_unique($classes) as $class) {
            if (class_exists($class)
                && ($rc = new \ReflectionClass($class)) && $rc->isSubclassOf(BaseApiModel::class)
                && !$rc->isAbstract()
            ) {
                $apiModels[] = $rc->getName();
            }
        }
        
        return $apiModels;
    }
}