<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Model\ORM;

/**
 * Class MultiRouteTrait.
 */
trait MultiRouteTrait
{
    protected $routes;

    /**
     * {@inheritdoc}
     */
    public function getRoutes()
    {
        $this->routes = is_array($this->routes) ? $this->routes : array();

        return $this->routes;
    }

    /**
     * {@inheritdoc}
     */
    public function addRoute($route)
    {
        $this->initRoutes();

        $found = false;
        foreach ($this->routes as $r){
            if($r->getId() == $route->getId()){
                $found = true;
                break;
            }
        }

        if(!$found){
            $this->routes[] = $route;
        }
//        $key = array_search($route, $this->routes);
//
//        if ($key === false) {
//            $this->routes[] = $route;
//        }

        return $this;
    }

    protected function initRoutes()
    {
        if (!$this->routes) {
            $this->routes = array();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRoute($route)
    {
        $this->initRoutes();

        $key = array_search($route, $this->routes);

        if ($key !== false) {
            unset($this->routes[$key]);
        }

        return $this;
    }
}
