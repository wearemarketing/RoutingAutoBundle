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
use WAM\Bundle\CoreBundle\Model\TranslationInterface;

/**
 * @ORM\Entity()
 */
class BlogTranslation implements TranslationInterface
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }
}
