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

    /**
     * @var \DateTime
     *
     * @JMS\Expose()
     * @JMS\Groups({"detail", "list"})
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $createdAt;

    /**
     * @var boolean
     */
    protected $userCanCreate = true;

    /**
     * @var boolean
     */
    protected $userCanUpdate = true;

    /**
     * @var boolean
     */
    protected $userCanDelete = true;

    /**
     * @JMS\VirtualProperty("authorization")
     * @JMS\Expose()
     * @JMS\Groups({"detail", "list"})
     */
    public function getAuthorization()
    {
        return [
            'update' => $this->userCanUpdate,
            'delete' => $this->userCanDelete
        ];
    }

    public function __clone()
    {
        $this->id = null;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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
     * @return \DateTime
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return bool
     */
    public function getUserCanCreate(): bool
    {
        return $this->userCanCreate;
    }

    /**
     * @param bool $userCanCreate
     *
     * @return $this
     */
    public function setUserCanCreate(bool $userCanCreate): self
    {
        $this->userCanCreate = $userCanCreate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUserCanUpdate(): bool
    {
        return $this->userCanUpdate;
    }

    /**
     * @param bool $userCanUpdate
     *
     * @return $this
     */
    public function setUserCanUpdate(bool $userCanUpdate): self
    {
        $this->userCanUpdate = $userCanUpdate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUserCanDelete(): bool
    {
        return $this->userCanDelete;
    }

    /**
     * @param bool $userCanDelete
     *
     * @return $this
     */
    public function setUserCanDelete(bool $userCanDelete): self
    {
        $this->userCanDelete = $userCanDelete;

        return $this;
    }

    /**
     * The function get the resource-specific path.
     *
     * @return string
     */
    abstract public static function getBaseApiPath();
}
