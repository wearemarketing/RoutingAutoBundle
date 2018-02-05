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
use Symfony\Cmf\Bundle\RoutingAutoBundle\Entity\AutoRoute;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Functional\Repository\DoctrineOrm;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Article;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\ConcreteContent;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Blog;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\ConflictProneArticle;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\Post;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\SeoArticle;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\SeoArticleMultilang;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use Symfony\Cmf\Component\Testing\Functional\DbManager\ORM;

/**
 * @group orm
 */
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
    public function testPersistBlogNoTranslatable()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlogNoTranslatable();

        $autoRoute = $repository->findAutoRoute('/blog/unit-testing-blog');

        $this->assertNotEmpty($autoRoute);

        /** @var \Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity\BlogNoTranslatable $blog */
        // make sure auto-route has been persisted
        $blog = $repository->findBlogNoTranslatable('Unit testing blog');
        $routes = $blog->getRoutes();

        $this->assertCount(1, $routes, 'There is no route associted with Entity');
        /** @var AutoRoute $route */
        $route = $routes[0];
        $this->assertInstanceOf(AutoRoute::class, $route);
        $locale = 'no-multilang';
        $this->assertContains('BlogNoTranslatable_'.$blog->getId().'_', $route->getName());
        $this->assertEquals('BlogNoTranslatable_'.$blog->getId(), $route->getCanonicalName());
        $this->assertEquals($locale, $route->getAutoRouteTag());
        $this->assertEquals('cmf_routing_auto.primary', $route->getType());
        $this->assertEquals(
            [
                '_controller' => 'BlogController',
            ],
            $route->getDefaults()
        );
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

        $this->assertCount(1, $routes, 'There is no route associted with Entity');
        /** @var AutoRoute $route */
        $route = $routes[0];
        $this->assertInstanceOf(AutoRoute::class, $route);
        $locale = 'en';
        $this->assertContains('Blog_'.$blog->getId().'_'.$locale.'_', $route->getName());
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
    public function testUpdateRenameBlogNoTranslatable($withPosts = false)
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlogNoTranslatable($withPosts);

        /** @var Blog $blog */
        $blog = $repository->findBlogNoTranslatable('Unit testing blog');
        // test update
        $blog->setTitle('Foobar');

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getObjectManager();
        $objectManager->persist($blog);
        $objectManager->flush();

        // note: The NAME stays the same, its the ID not the title
        $blog = $repository->findBlogNoTranslatable('Foobar');
        $this->assertNotNull($blog);
        $routes = $blog->getRoutes();
        $this->assertCount(1, $routes);

        // How to have to be the new route
        /** @var AutoRoute $route */
        $newRoute = $routes[0];
        $this->assertEquals('BlogNoTranslatable_'.$blog->getId(), $newRoute->getCanonicalName());
        $this->assertInstanceOf(AutoRoute::class, $newRoute);
        $this->assertContains('BlogNoTranslatable_'.$blog->getId().'_', $newRoute->getName());
        $this->assertEquals('/blog/foobar', $newRoute->getStaticPrefix());

        if ($withPosts) {
            /** @var Post $post */
            $post = $repository->findPostNoTranslatable('This is a post title');
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
        $this->assertContains('Blog_'.$blog->getId().'_en_', $newRoute->getName());
        $this->assertEquals('/blog/foobar', $newRoute->getStaticPrefix());

        // How to have to be the old route
        /** @var AutoRoute $route */
        $oldRoute = $routes[0];
        $this->assertEquals('Blog_'.$blog->getId(), $oldRoute->getCanonicalName());
        $this->assertInstanceOf(AutoRoute::class, $oldRoute);
        $this->assertContains('Blog_'.$blog->getId().'_en_', $oldRoute->getName());
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

    public function testUpdatePostNoTranslatableNotChangingTitle()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlogNoTranslatable(true);

        /** @var Post $post */
        $post = $repository->findPostNoTranslatable('This is a post title');
        $this->assertNotNull($post);

        $post->setBody('Test');

        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $post = $repository->findPostNoTranslatable('This is a post title');
        $routes = $post->getRoutes();

        $this->assertCount(1, $routes);
        /** @var AutoRoute $route */
        $route = $routes[0];
        $this->assertInstanceOf(AutoRoute::class, $route);

        $this->assertEquals('/blog/unit-testing-blog/2013/03/21/this-is-a-post-title', $route->getStaticPrefix());
    }

    public function testUpdatePostNotChangingTitle()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog(true);

        /** @var Post $post */
        $post = $repository->findPost('This is a post title');
        $this->assertNotNull($post);

        $post->setBody('Test');

        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $post = $repository->findPost('This is a post title');
        $routes = $post->getRoutes();

        $this->assertCount(1, $routes);
        /** @var AutoRoute $route */
        $route = $routes[0];
        $this->assertInstanceOf(AutoRoute::class, $route);

        $this->assertEquals('/blog/unit-testing-blog/2013/03/21/this-is-a-post-title', $route->getStaticPrefix());
    }

    public function testRemoveBlog()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog();

        $blog = $repository->findBlog('Unit testing blog');

        // test removing
        $this->getObjectManager()->remove($blog);

        $this->getObjectManager()->flush();

        $routes = $repository->findRoutesForBlog($blog);
        $this->assertEmpty($routes);

        // We should test when the blog has post. But it will be the same for ass
        // because we do not propagate the changes to children
    }

    public function testRemoveBlogNoTranslatable()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlogNoTranslatable();

        $blog = $repository->findBlogNoTranslatable('Unit testing blog');

        // test removing
        $this->getObjectManager()->remove($blog);

        $this->getObjectManager()->flush();

        $routes = $repository->findRoutesForBlog($blog);
        $this->assertEmpty($routes);

        // We should test when the blog has post. But it will be the same for ass
        // because we do not propagate the changes to children
    }

    public function testPersistPost()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog(true);

        $route = $repository->findAutoRoute('/blog/unit-testing-blog/2013/03/21/this-is-a-post-title');
        $this->assertNotNull($route);

        // make sure auto-route references content
        /** @var Post $post */
        $post = $repository->findPost('This is a post title');
        $routes = $post->getRoutes();
        $this->assertCount(1, $routes);
        /** @var AutoRoute $route */
        $route = $routes[0];

        $this->assertSame(get_class($post), $route->getContentClass());
        $this->assertEquals(['id' => $post->getId()], $route->getContentId());
        $this->assertInstanceOf(AutoRoute::class, $route);
        $this->assertContains('Post_'.$post->getId().'_en_', $route->getName());
    }

    public function testPersistPostNoTranslatable()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlogNoTranslatable(true);

        $route = $repository->findAutoRoute('/blog/unit-testing-blog/2013/03/21/this-is-a-post-title');
        $this->assertNotNull($route);

        // make sure auto-route references content
        /** @var Post $post */
        $post = $repository->findPostNoTranslatable('This is a post title');
        $routes = $post->getRoutes();
        $this->assertCount(1, $routes);
        /** @var AutoRoute $route */
        $route = $routes[0];

        $this->assertSame(get_class($post), $route->getContentClass());
        $this->assertEquals(['id' => $post->getId()], $route->getContentId());
        $this->assertInstanceOf(AutoRoute::class, $route);
        $this->assertContains('PostNoTranslatable_'.$post->getId().'_', $route->getName());
    }

    public function testUpdatePost()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlog(true);

        // make sure auto-route references content
        /** @var Post $post */
        $post = $repository->findPost('This is a post title');
        $post->setTitle('This is different');

        $post->setDate(new \DateTime('2014-01-25'));

        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();

        $routes = $repository->findRoutesForPost($post);

        // It has to be 1, but do not have implemented the behavior
        // that remove the previous route when we change a entity auto routable
        $this->assertCount(2, $routes);
        /** @var AutoRoute $route */
        $route = $routes[1];

        $this->assertInstanceOf(AutoRoute::class, $route);
        $this->assertContains('Post_'.$post->getId().'_en_', $route->getName());

        $this->assertEquals('/blog/unit-testing-blog/2014/01/25/this-is-different', $route->getStaticPrefix());
    }

    public function testUpdatePostNoTranslatable()
    {
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $repository->createBlogNoTranslatable(true);

        // make sure auto-route references content
        /** @var Post $post */
        $post = $repository->findPostNoTranslatable('This is a post title');
        $post->setTitle('This is different');

        $post->setDate(new \DateTime('2014-01-25'));

        $this->getObjectManager()->persist($post);
        $this->getObjectManager()->flush();

        $routes = $repository->findRoutesForPostNoTranslatable($post);

        $this->assertCount(1, $routes);
        /** @var AutoRoute $route */
        $route = $routes[0];

        $this->assertInstanceOf(AutoRoute::class, $route);
        $this->assertContains('PostNoTranslatable_'.$post->getId().'_', $route->getName());

        $this->assertEquals('/blog/unit-testing-blog/2014/01/25/this-is-different', $route->getStaticPrefix());
    }

    public function provideMultilangArticle()
    {
        return [
            [
                [
                    'en' => 'Hello everybody!',
                    'fr' => 'Bonjour le monde!',
                    'de' => 'Gutentag',
                    'es' => 'Hola todo el mundo',
                ],
                [
                    '/articles/en/hello-everybody',
                    '/articles/fr/bonjour-le-monde',
                    '/articles/de/gutentag',
                    '/articles/es/hola-todo-el-mundo',

                    '/articles/en/hello-everybody-edit',
                    '/articles/fr/bonjour-le-monde-edit',
                    '/articles/de/gutentag-edit',
                    '/articles/es/hola-todo-el-mundo-edit',

                    '/articles/en/hello-everybody-review',
                    '/articles/fr/bonjour-le-monde-review',
                    '/articles/de/gutentag-review',
                    '/articles/es/hola-todo-el-mundo-review',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideMultilangArticle
     */
    public function testMultilangArticle($data, $expectedPaths)
    {
        $article = new Article();
        $article->setTitle('Article 1');
        $article->mergeNewTranslations();
        $this->getObjectManager()->persist($article);

        foreach ($data as $lang => $title) {
            $article->setLocale($lang);
            $article->setTitle($title);
        }

        $article->mergeNewTranslations();

        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $locales = array_keys($data);

        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        foreach ($expectedPaths as $i => $expectedPath) {
            $localeIndex = $i % count($locales);
            $expectedLocale = $locales[$localeIndex];

            /** @var AutoRoute $route */
            $route = $repository->findAutoRoute($expectedPath);

            $this->assertNotNull($route, 'Route: '.$expectedPath);
            $this->assertInstanceOf(AutoRoute::class, $route);
            $this->assertEquals($expectedLocale, $route->getLocale());

            /** @var Article $content */
            $content = $repository->findContent($route);

            $this->assertNotNull($content);
            $this->assertInstanceOf(Article::class, $content);

            // We haven't loaded the translation for the document, so it is always in the default language
            $this->assertEquals('Hello everybody!', $content->getTitle());
        }
    }

    public function provideUpdateMultilangArticle()
    {
        return [
            [
                [
                    'en' => 'Hello everybody!',
                    'fr' => 'Bonjour le monde!',
                    'de' => 'Gutentag',
                    'es' => 'Hola todo el mundo',
                ],
                [
                    '/articles/en/hello-everybody',
                    '/articles/fr/bonjour-le-monde',
                    '/articles/de/gutentag-und-auf-wiedersehen',
                    '/articles/es/hola-todo-el-mundo',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideUpdateMultilangArticle
     */
    public function testUpdateMultilangArticle($data, $expectedPaths)
    {
        $article = new Article();
        $article->setTitle('Article 1');
        $this->getObjectManager()->persist($article);

        foreach ($data as $lang => $title) {
            $article->setLocale($lang);
            $article->setTitle($title);
        }

        $article->mergeNewTranslations();

        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $title = 'Gutentag';
        $locale = 'de';
        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        /** @var Article $article */
        $article = $repository->findArticle($title, $locale);
        $article->setLocale($locale);
        $germanTitle = $article->getTitle() . '-und-auf-wiedersehen';
        $article->setTitle($germanTitle);
        $article->mergeNewTranslations();
        $this->getObjectManager()->persist($article);

        $this->getObjectManager()->flush();

        $article = $repository->findArticle($germanTitle, $locale);
        $routes = $repository->findRoutesForArticle($article);

        // Multiply the expected paths by 3 because Article has 3 routes defined.
        $this->assertCount(count($data) * 3, $routes);

        $this->getObjectManager()->clear();

        foreach ($expectedPaths as $expectedPath) {
            /** @var AutoRoute $route */
            $route = $repository->findAutoRoute($expectedPath);

            $this->assertNotNull($route);
            $this->assertInstanceOf(AutoRoute::class, $route);

            $content = $repository->findContent($route);

            $this->assertNotNull($content);
            $this->assertInstanceOf(Article::class, $content);

            // We haven't loaded the translation for the document, so it is always in the default language
            $this->assertEquals('Hello everybody!', $content->getTitle());
        }
    }

    public function testResolveConflictOnSingleMultilangArticle()
    {
        $article = new ConflictProneArticle();
        $article->setLocale('fr');
        $article->setTitle('Weekend');
        $article->mergeNewTranslations();
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        $article->setLocale('en');
        $article->setTitle('Weekend');
        $article->mergeNewTranslations();
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        $route = $repository->findAutoRoute('/conflict-prone-articles/weekend');
        $this->assertNotNull($route);

        $route = $repository->findAutoRoute('/conflict-prone-articles/weekend-1');
        $this->assertNotNull($route);
    }

    public function provideLeaveRedirect()
    {
        return [
            [
                [
                    'en' => 'Hello everybody!',
                    'fr' => 'Bonjour le monde!',
                    'de' => 'Gutentag',
                    'es' => 'Hola todo el mundo',
                ],
                [
                    'en' => 'Goodbye everybody!',
                    'fr' => 'Aurevoir le monde!',
                    'de' => 'Auf weidersehn',
                    'es' => 'Adios todo el mundo',
                ],
                [
                    '/seo-articles/en/hello-everybody',
                    '/seo-articles/fr/bonjour-le-monde',
                    '/seo-articles/de/gutentag',
                    '/seo-articles/hola-todo-el-mundo',
                ],
                [
                    '/seo-articles/en/goodbye-everybody',
                    '/seo-articles/fr/aurevoir-le-monde',
                    '/seo-articles/de/auf-weidersehn',
                    '/seo-articles/es/adios-todo-el-mundo',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideLeaveRedirect
     */
    public function testLeaveRedirect($data, $updatedData, $expectedRedirectRoutePaths, $expectedAutoRoutePaths)
    {
        $this->markTestSkipped('Something does not work properly. When it is updated values generate duplicated routes');

        $article = new SeoArticleMultilang();

        foreach ($data as $lang => $title) {
            $article->setLocale($lang);
            $article->setTitle($title);
        }

        $article->mergeNewTranslations();

        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
        $this->getObjectManager()->clear();

        $repository = $this->getObjectManager()->getRepository(SeoArticleMultilang::class);
        /** @var SeoArticleMultilang $article */
        $article = $repository->find($article->getId());
        foreach ($updatedData as $lang => $title) {
            $article->setLocale($lang);
            $article->setTitle($title);
        }

        $article->mergeNewTranslations();

        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        /** @var DoctrineOrm $repository */
        $repository = $this->getRepository();
        foreach ($expectedRedirectRoutePaths as $originalPath) {
            $redirectRoute = $repository->findAutoRoute($originalPath);
            $this->assertNotNull($redirectRoute, 'Redirect exists for: '.$originalPath);
            $this->assertEquals(AutoRouteInterface::TYPE_REDIRECT, $redirectRoute->getType());
        }

        foreach ($expectedAutoRoutePaths as $newPath) {
            $autoRoute = $repository->findAutoRoute($newPath);
            $this->assertNotNull($autoRoute, 'Autoroute exists for: '.$newPath);
            $this->assertEquals(AutoRouteInterface::TYPE_PRIMARY, $autoRoute->getType());
        }
    }

    public function testLeaveRedirectAndRenameToOriginal()
    {
        $article = new SeoArticle();
        $article->setTitle('Hai');
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        $article->title = 'Ho';
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();

        $article->title = 'Hai';
        $this->getObjectManager()->persist($article);
        $this->getObjectManager()->flush();
    }

    /**
     * Leave direct should migrate children.
     */
    public function testLeaveRedirectChildrenMigrations()
    {
        $this->markTestSkipped('At the moment we do not propagate the changes to children');
    }

    /**
     * Ensure that we can map parent classes: #56.
     */
    public function testParentClassMapping()
    {
        $content = new ConcreteContent();
        $content->setTitle('Hello');
        $this->getObjectManager()->persist($content);
        $this->getObjectManager()->flush();

        $this->getObjectManager()->refresh($content);

        $routes = $content->getRoutes();

        $this->assertCount(1, $routes);

        // Alse, We test if the route is right
        /** @var AutoRoute $route */
        $route = $routes[0];
        $this->assertEquals('cmf_routing_auto.primary', $route->getType());
        $this->assertEquals('no-multilang', $route->getTag());
        $this->assertEquals('/articles/hello', $route->getStaticPrefix());
        $this->assertEmpty($route->getDefaults());

        // Maybe... we have to test the same but with a translatable entity
    }

    public function testConflictResolverAutoIncrement()
    {
        $this->markTestSkipped('Working...');
        $this->createBlog();
        $blog = $this->getDm()->find(null, '/test/test-blog');

        $post = new Post();
        $post->name = 'Post 1';
        $post->title = 'Same Title';
        $post->blog = $blog;
        $post->date = new \DateTime('2013/03/21');
        $this->getDm()->persist($post);
        $this->getDm()->flush();

        $post = new Post();
        $post->name = 'Post 2';
        $post->title = 'Same Title';
        $post->blog = $blog;
        $post->date = new \DateTime('2013/03/21');
        $this->getDm()->persist($post);
        $this->getDm()->flush();

        $post = new Post();
        $post->name = 'Post 3';
        $post->title = 'Same Title';
        $post->blog = $blog;
        $post->date = new \DateTime('2013/03/21');
        $this->getDm()->persist($post);
        $this->getDm()->flush();

        $expectedRoutes = [
            '/test/auto-route/blog/unit-testing-blog/2013/03/21/same-title',
            '/test/auto-route/blog/unit-testing-blog/2013/03/21/same-title-1',
            '/test/auto-route/blog/unit-testing-blog/2013/03/21/same-title-2',
        ];

        foreach ($expectedRoutes as $expectedRoute) {
            $route = $this->getDm()->find('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $expectedRoute);
            $this->assertNotNull($route);
        }
    }

    public function testCreationOfChildOnRoot()
    {
        $this->markTestSkipped('Working...');
        $page = new Page();
        $page->title = 'Home';
        $page->path = '/test/home';
        $this->getDm()->persist($page);
        $this->getDm()->flush();

        $expectedRoute = '/test/auto-route/home';
        $route = $this->getDm()->find('Symfony\Cmf\Bundle\RoutingAutoBundle\Model\AutoRoute', $expectedRoute);

        $this->assertNotNull($route);
    }

    /**
     * @expectedException \Symfony\Cmf\Component\RoutingAuto\ConflictResolver\Exception\ExistingUriException
     */
    public function testConflictResolverDefaultThrowException()
    {
        $this->markTestSkipped('Working...');
        $blog = new Blog();
        $blog->path = '/test/test-blog';
        $blog->title = 'Unit testing blog';
        $this->getDm()->persist($blog);
        $this->getDm()->flush();

        $blog = new Blog();
        $blog->path = '/test/test-blog-the-second';
        $blog->title = 'Unit testing blog';
        $this->getDm()->persist($blog);
        $this->getDm()->flush();
    }

    public function testGenericNodeShouldBeConvertedInAnAutoRouteNode()
    {
        $this->markTestSkipped('Working...');
        $blog = new Blog();
        $blog->path = '/test/my-post';
        $blog->title = 'My Post';
        $this->getDm()->persist($blog);
        $this->getDm()->flush();

        $this->assertInstanceOf(
            'Doctrine\ODM\PHPCR\Document\Generic',
            $this->getDm()->find(null, '/test/auto-route/blog')
        );
        $blogRoute = $this->getDm()->find(null, '/test/auto-route/blog/my-post');
        $this->assertInstanceOf('Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface', $blogRoute);
        $this->assertSame($blog, $blogRoute->getContent());

        $page = new Page();
        $page->path = '/test/blog';
        $page->title = 'Blog';

        $this->getDm()->persist($page);
        $this->getDm()->flush();

        $this->assertInstanceOf(
            'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface',
            $this->getDm()->find(null, '/test/auto-route/blog')
        );
        $this->assertInstanceOf(
            'Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface',
            $this->getDm()->find(null, '/test/auto-route/blog/my-post')
        );
    }
}
