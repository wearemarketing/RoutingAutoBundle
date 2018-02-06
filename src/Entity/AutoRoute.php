<?php

namespace Symfony\Cmf\Bundle\RoutingAutoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Cmf\Bundle\RoutingBundle\Model\Route;
use Symfony\Cmf\Component\RoutingAuto\Model\AutoRouteInterface;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

/**
 * TODO: create 2 tables or only one
 * TODO: if we create one table, which name for all routes and auto routes
 * TODO: Add all index as in Orm\Route
 *
 * @ORM\Entity(repositoryClass="Symfony\Cmf\Bundle\RoutingAutoBundle\Repository\AutoRouteRepository")
 * @ORM\Table(name="orm_auto_routes")
 */
class AutoRoute extends Route implements AutoRouteInterface
{
    const CONTENT_CLASS_KEY = 'contentClass';
    const CONTENT_ID_KEY = 'contentId';
    const DEFAULT_KEY_AUTO_ROUTE_LOCALE = '_route_auto_tag';

    /**
     * Identifier.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", unique=true)
     */
    protected $name;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position = 0;

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
     * @return string
     */
    public function __toString()
    {
        return $this->getPath();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the sort order of this route.
     *
     * @param int $position
     *
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get the sort order of this route.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Getter for CanonicalName.
     *
     * @return string
     */
    public function getCanonicalName()
    {
        return $this->canonicalName;
    }

    /**
     * Setter for CanonicalName.
     *
     * @param string $canonicalName
     *
     * @return $this
     */
    public function setCanonicalName($canonicalName)
    {
        $this->canonicalName = $canonicalName;

        return $this;
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
        return $this->getDefault(self::DEFAULT_KEY_AUTO_ROUTE_LOCALE);
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->setDefault(self::DEFAULT_KEY_AUTO_ROUTE_LOCALE, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($mode)
    {
        $this->setDefault('type', $mode);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getDefault('type');
    }
}
