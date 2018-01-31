<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Enhancer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Entity\AutoRoute;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route;
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
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager)
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
            $this->resolveRouteContent($routeObject);
        }

        return $defaults;
    }

    /**
     * Resolves the route content.
     *
     * @param Route $routeObject
     */
    public function resolveRouteContent(AutoRoute $routeObject)
    {
        // TODO: Extract this code in a repository instead of enhancer

        $class = $routeObject->getContentClass();
        $id = $routeObject->getContentId();
        if (empty($class) or empty($id)) {
            return;
        }

        $objectRepository = $this->manager->getRepository($class);
        $object = $objectRepository->find($id);
        if ($object instanceof TranslatableInterface) {
            $object->setCurrentLocale($routeObject->getDefault('_locale'));
        }

        $routeObject->setContent($object);
    }
}
