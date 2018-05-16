<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Cmf\Component\Testing\HttpKernel\TestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends TestKernel
{
    public function configure()
    {
        $this->requireBundleSet('default');

        if ($this->isOrmEnv()) {
            $this->requireBundleSet('doctrine_orm');
        } else {
            $this->requireBundleSet('phpcr_odm');
        }

        $this->addBundles([
            new \Symfony\Cmf\Bundle\RoutingBundle\CmfRoutingBundle(),
            new \Symfony\Cmf\Bundle\RoutingAutoBundle\CmfRoutingAutoBundle(),

            new \WAM\Bundle\RoutingBundle\WAMRoutingBundle(),

            new \FOS\UserBundle\FOSUserBundle(),
            new \Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new \Sonata\EasyExtendsBundle\SonataEasyExtendsBundle(),

            new \Sonata\CoreBundle\SonataCoreBundle(),
            new \Sonata\AdminBundle\SonataAdminBundle(),
            new \Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new \Sonata\UserBundle\SonataUserBundle(),
            new \Sonata\SeoBundle\SonataSeoBundle(),
            new \Sonata\MediaBundle\SonataMediaBundle(),
            new \Sonata\ClassificationBundle\SonataClassificationBundle(),
            new \Sonata\TranslationBundle\SonataTranslationBundle(),
            new \Sonata\BlockBundle\SonataBlockBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),


            new \Symfony\Cmf\Bundle\CoreBundle\CmfCoreBundle(),

            new \WAM\Bundle\CoreBundle\WAMCoreBundle(),
            new \WAM\Bundle\UserBundle\WAMUserBundle(),
            new \WAM\Bundle\LocaleBundle\WAMLocaleBundle(),
            new \WAM\Bundle\MediaBundle\WAMMediaBundle(),
            new \WAM\Bundle\ClassificationBundle\WAMClassificationBundle(),
            new \WAM\Bundle\BlockBundle\WAMBlockBundle(),

            new \Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Bundle\TestBundle\TestBundle(),
        ]);
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        if ($this->isOrmEnv()) {
            $loader->import(__DIR__.'/config/default.php');
            $loader->import(__DIR__.'/config/parameters.yml');
            $loader->import(__DIR__.'/config/doctrine_orm.yml');
            $loader->import(__DIR__.'/config/extra_config.yml');
        } else {
            $loader->import(CMF_TEST_CONFIG_DIR.'/default.php');
            $loader->import(CMF_TEST_CONFIG_DIR.'/phpcr_odm.php');
            $loader->import(__DIR__.'/config/doctrine_phpcr_odm.yml');
        }
    }

    protected function buildContainer()
    {
        $container = parent::buildContainer();
        $container->setParameter('cmf_testing.bundle_fqn', 'Symfony\Cmf\Bundle\RoutingAutoBundle');

        return $container;
    }

    /**
     * @return bool
     */
    private function isOrmEnv()
    {
        return 'doctrine_orm' === $this->environment or 'orm' === $this->environment;
    }
}
