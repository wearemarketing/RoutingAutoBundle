<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Entity\AutoRoute;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;

/**
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 */
class RouteListener
{
    private $routeClassName;

    public function __construct($routeClassName)
    {
        $this->routeClassName = $routeClassName;
    }

    /**
     * This listener is responsible of loading autoRoutes and attach it to its content entities.
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();

        if ($entity instanceof RouteReferrersInterface) {
            $entityManager = $eventArgs->getEntityManager();
            $className = $this->getClassName($entity);
            $id = $entityManager->getClassMetadata($className)->getIdentifierValues($entity);

            //this workaround is needed to bypass doctrine escaping parameters
            $dql = sprintf(
                "select o from %s o WHERE o.%s = '%s' and o.%s = '%s' order by o.position",
                $this->routeClassName,
                AutoRoute::CONTENT_CLASS_KEY,
                $className,
                AutoRoute::CONTENT_ID_KEY,
                json_encode($id)
            );

            $routes = $entityManager->createQuery($dql)->getResult();

            foreach ($routes as $route) {
                $entity->addRoute($route);
            }
        }
    }

    /**
     * Sometimes $entity is a doctrine proxy and we need to retrieve the real entity FQCN.
     *
     * @param $entity
     *
     * @return string
     */
    private function getClassName($entity)
    {
        return $entity instanceof \Doctrine\ORM\Proxy\Proxy ?
            get_parent_class($entity) :
            get_class($entity);
    }
}
