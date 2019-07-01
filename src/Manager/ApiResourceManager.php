<?php

namespace Hessnatur\SimpleRestCRUDBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class ApiResourceManager implements ApiResourceManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function update(ApiResource $apiResource): void
    {
        $this->entityManager->persist($apiResource);
        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function remove(ApiResource $apiResource): void
    {
        $this->entityManager->remove($apiResource);
        $this->entityManager->flush();
    }

    /**
     * @param string $entityClass
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository(string $entityClass)
    {
        return $this->entityManager->getRepository($entityClass);
    }
}
