<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Model\ORM\MultiRouteTrait;
use Symfony\Cmf\Bundle\RoutingAutoBundle\Tests\Resources\Model\Post as ModelPost;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;

/**
 * @ORM\Entity()
 */
class PostNoTranslatable extends ModelPost implements RouteReferrersInterface
{
    use MultiRouteTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $body;

    /**
     * @ORM\Column(type="date")
     *
     * @var \DateTime
     */
    protected $date;

    /**
     * @ORM\ManyToOne(targetEntity="BlogNoTranslatable")
     *
     * @var BlogNoTranslatable
     */
    protected $blog;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    protected $title;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return BlogNoTranslatable
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param BlogNoTranslatable $blog
     */
    public function setBlog($blog)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getBlogTitle()
    {
        return $this->getBlog()->getTitle();
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }
}
