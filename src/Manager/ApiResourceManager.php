<?php

namespace Hessnatur\SimpleRestCRUDBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Hessnatur\SimpleRestCRUDBundle\Repository\ApiResourceRepositoryInterface;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class ApiResourceManager implements ApiResourceManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
     * @return \Doctrine\Common\Persistence\ObjectRepository|ApiResourceRepositoryInterface
     */
    public function getRepository(string $entityClass): ApiResourceRepositoryInterface
    {
        return $this->entityManager->getRepository($entityClass);
    }
}
