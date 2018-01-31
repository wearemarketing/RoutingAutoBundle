<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Orm\Route;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;

/**
 * @ORM\Entity()
 * @ORM\Table(name="orm_auto_route")
 *
 * @author Noel Garcia <ngarcia@wearemarketing.com>
 */
class AutoRoute extends Route implements AutoRouteInterface
{
    const CONTENT_CLASS_KEY = 'contentClass';
    const CONTENT_ID_KEY = 'contentId';

    /**
     * @ORM\Column(type="string", length=100)
     *
     * @var string
     */
    protected $canonicalName;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @var string
     */
    private $contentClass;

    /**
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @var array
     */
    private $contentId;

    /**
     * @ORM\Column(type="string", length=30)
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=12, nullable=true)
     *
     * @var string
     */
    protected $tag;

    /**
     * AutoRoute constructor.
     *
     *
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->type = AutoRouteInterface::TYPE_PRIMARY;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function setAutoRouteTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutoRouteTag()
    {
        return $this->tag;
    }

    /**
     * {@inheritdoc}
     */
    public function setRedirectTarget($autoTarget)
    {
        $this->setDefault('_controller', 'FrameworkBundle:Redirect:redirect');
        $this->setDefault('route', $autoTarget->getName());
        $this->setDefault('permanent', true);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectTarget()
    {
        return $this->getDefault('route');
    }

    /**
     * Getter for content class.
     *
     * @return string
     */
    public function getContentClass()
    {
        return $this->contentClass;
    }

    /**
     * Setter for content class.
     *
     * @param string $class
     *
     * @return $this
     */
    public function setContentClass($class)
    {
        $this->contentClass = $class;

        return $this;
    }

    /**
     * Getter for content class.
     *
     * @return mixed
     */
    public function getContentId()
    {
        return $this->contentId;
    }

    /**
     * Setter for content id.
     *
     * @param mixed $id
     *
     * @return $this
     */
    public function setContentId(array $id)
    {
        $this->contentId = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->tag;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->tag = $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($mode)
    {
        $this->type = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
