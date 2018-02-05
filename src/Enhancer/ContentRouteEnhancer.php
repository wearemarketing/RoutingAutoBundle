<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Enhancer;

use Doctrine\ORM\EntityManagerInterface;
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
     * @var \Doctrine\Common\Persistence\ObjectRepository|\Symfony\Cmf\Bundle\RoutingAutoBundle\Repository\AutoRouteRepository
     */
    private $repository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->repository = $manager->getRepository(AutoRoute::class);
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
            $this->repository->resolveRouteContent($routeObject);
        }

        return $defaults;
    }
}
