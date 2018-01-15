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

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Repository\DoctrineOrm;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Post;
use Symfony\Cmf\Component\Testing\Functional\DbManager\ORM;
use WAM\Bundle\RoutingBundle\Entity\AutoRoute;

class DoctrineOrmAutoRouteListenerTest extends ListenerTestCase
{
    public function getKernelConfiguration()
    {
        return [
            'environment' => 'doctrine_orm',
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        /** @var ORM $dbManager */
        $dbManager = $this->getDbManager('ORM');
        $dbManager->purgeDatabase();
    }

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
        /** @var AutoRoute $route */
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

    public function provideTestUpdateBlog()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider provideTestUpdateBlog
     */
    public function testUpdateRenameBlog($withPosts = false)
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog($withPosts);

        /** @var Blog $blog */
        $blog = $repository->findBlog('Unit testing blog');
        // test update
        $blog->setTitle('Foobar');
        $blog->mergeNewTranslations();

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getObjectManager();
        $objectManager->persist($blog);
        $objectManager->flush();

        // note: The NAME stays the same, its the ID not the title
        $blog = $repository->findBlog('Foobar');
        $this->assertNotNull($blog);
        $routes = $blog->getRoutes();
        $this->assertCount(2, $routes);

        // How to have to be the new route
        /** @var AutoRoute $route */
        $newRoute = $routes[1];
        $this->assertEquals('Blog_'.$blog->getId(), $newRoute->getCanonicalName());
        $this->assertInstanceOf(AutoRoute::class, $newRoute);
        $this->assertContains('Blog_'.$blog->getId(). '_en_', $newRoute->getName());
        $this->assertEquals('/blog/foobar', $newRoute->getStaticPrefix());

        // How to have to be the old route
        /** @var AutoRoute $route */
        $oldRoute = $routes[0];
        $this->assertEquals('Blog_'.$blog->getId(), $oldRoute->getCanonicalName());
        $this->assertInstanceOf(AutoRoute::class, $oldRoute);
        $this->assertContains('Blog_'.$blog->getId(). '_en_', $oldRoute->getName());
        $this->assertEquals('/blog/unit-testing-blog', $oldRoute->getStaticPrefix());
        $this->assertEquals('en', $oldRoute->getAutoRouteTag());
        $this->assertEquals('cmf_routing_auto.redirect', $oldRoute->getType());
        $this->assertEquals(
            [
                '_controller' => 'FrameworkBundle:Redirect:redirect',
                '_locale' => 'en',
                'route' => $newRoute->getName(),
                'permanent' => true,
            ],
            $oldRoute->getDefaults()
        );

        if ($withPosts) {
            /** @var Post $post */
            $post = $repository->findPost('This is a post title');
            $this->assertNotNull($post);

            $routes = $post->getRoutes();
            /** @var AutoRoute $route */
            $route = $routes[0];
            $this->assertNotNull($route);
            $this->getObjectManager()->refresh($route);

            // That is not completely right. Has to be /blog/foobar/2013 but with orm
            // when blog is updated doesn't propagate the change to the "children"
            $this->assertEquals(
                '/blog/unit-testing-blog/2013/03/21/this-is-a-post-title',
                $route->getStaticPrefix()
            );
        }
    }
}
