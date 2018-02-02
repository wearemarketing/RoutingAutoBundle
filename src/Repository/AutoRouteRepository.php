<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Entity\AutoRoute;

class AutoRouteRepository extends EntityRepository
{
    public function resolveRouteContent(AutoRoute $autoRoute)
    {
        $class = $autoRoute->getContentClass();
        $id = $autoRoute->getContentId();
        if (empty($class) or empty($id)) {
            return;
        }

        $objectRepository = $this->_em->getRepository($class);
        $object = $objectRepository->find($id);
        if ($object instanceof TranslatableInterface) {
            $object->setCurrentLocale($autoRoute->getDefault('_locale'));
        }

        $autoRoute->setContent($object);
    }
}
