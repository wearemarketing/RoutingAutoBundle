<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Enhancer;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Adapter\OrmAdapter;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Entity\AutoRoute;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Cmf\Component\Routing\Enhancer\RouteEnhancerInterface;

/**
 * This enhancer injects resolves content data.
 *
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 */
class ContentRouteEnhancer implements RouteEnhancerInterface
{
    /**
     * @var OrmAdapter
     */
    private $manager;

    public function __construct(OrmAdapter $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param array   $defaults
     * @param Request $request
     *
     * @return array
     */
    public function enhance(array $defaults, Request $request)
    {
        $routeObject = $defaults[RouteObjectInterface::ROUTE_OBJECT];

        if ($routeObject instanceof AutoRoute) {
            $this->manager->resolveRouteContent($routeObject);
        }

        return $defaults;
    }
}
