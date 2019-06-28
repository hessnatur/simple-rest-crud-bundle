<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @author Felix Niedballa <felix.niedballa@hess-natur.de>
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class ApiResource
{
    /**
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Groups({"detail", "list"})
     *
     * @ORM\Id
     * @ORM\Column(type="string", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    protected $id;

    /**
     * @var string
     *
     * @JMS\Expose()
     * @JMS\Groups({"detail", "list"})
     */
    protected $self;

    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * The function sets the link to the object which implements this interface.
     *
     * @param string $self
     *
     * @return $this
     */
    public function setSelf(string $self)
    {
        $this->self = $self;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getSelf(): ?string
    {
        return $this->self;
    }

    /**
     * The function get the resource-specific path.
     *
     * @return string
     */
    abstract public function getApiPath();
}
