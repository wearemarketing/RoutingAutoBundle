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
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\ORM\MultiRouteTrait;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;

/**
 * @ORM\Entity()
 */
class Page extends SeoArticle
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;
}
