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

        if (!in_array($route, $this->routes)) {
            $this->routes[] = $route;
        }

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
        if ($key = array_search($route, $this->routes)) {
            unset($this->routes[$key]);
        }

        return $this;
    }
}
