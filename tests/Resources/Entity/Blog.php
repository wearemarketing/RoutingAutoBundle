<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2017 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Model\Blog as ModelBlog;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use WAM\Bundle\RoutingBundle\Model\MultiRouteTrait;

/**
 * @ORM\Entity()
 */
class Blog extends ModelBlog implements RouteReferrersInterface, TranslatableInterface
{
    use MultiRouteTrait;
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->translate(null, false)->getTitle();
    }

    public function setTitle($title)
    {
        return $this->translate(null, false)->setTitle($title);
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->setCurrentLocale($locale);

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->getCurrentLocale();
    }
}
