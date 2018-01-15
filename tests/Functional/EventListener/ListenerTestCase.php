<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\EventListener;

use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\OrmBaseTestCase;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Repository\DoctrineOrm;
use WAM\Bundle\RoutingBundle\Entity\AutoRoute;

abstract class ListenerTestCase extends OrmBaseTestCase
{
    /**
     * It should persist the blog document and create an auto route.
     * It should set the defaults on the route.
     */
    public function testPersistBlog()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog();

        $autoRoute = $repository->findAutoRoute('/blog/unit-testing-blog');

        $this->assertNotEmpty($autoRoute);

        /** @var \Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Blog $blog */
        // make sure auto-route has been persisted
        $blog = $repository->findBlog('Unit testing blog');
        $routes = $blog->getRoutes();

        $this->assertCount(1, $routes, "There is no route associted with Entity");
        $route = $routes[0];
        $this->assertInstanceOf(AutoRoute::class, $route);
        $locale = 'en';
        $this->assertContains('Blog_'.$blog->getId(). '_' . $locale . '_', $route->getName());
        $this->assertEquals('Blog_'.$blog->getId(), $route->getCanonicalName());
        $this->assertEquals($locale, $route->getAutoRouteTag());
        $this->assertEquals('cmf_routing_auto.primary', $route->getType());
        $this->assertEquals(
            [
                '_controller' => 'BlogController',
                '_locale' => 'en',
            ],
            $route->getDefaults()
        );
    }
}
