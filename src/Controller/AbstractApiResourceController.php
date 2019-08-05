<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Hessnatur\SimpleRestCRUDBundle\Event\ApiResourceEvent;
use Hessnatur\SimpleRestCRUDBundle\HessnaturSimpleRestCRUDEvents;
use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManager;
use Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManagerInterface;
use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Hessnatur\SimpleRestCRUDBundle\Repository\ApiResourceRepositoryInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Felix Niedballa <felix.niedballa@hess-natur.de>
 */
abstract class AbstractApiResourceController
{
    /**
     * @var ApiResourceManagerInterface
     */
    protected $apiResourceManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var FilterBuilderUpdaterInterface
     */
    protected $filterBuilderUpdater;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var ViewHandlerInterface
     */
    protected $viewHandler;

    /**
     * @var ParameterBagInterface
     */
    protected $parameterBag;

    /**
     * @param ApiResourceManagerInterface   $apiResourceManager
     * @param EventDispatcherInterface      $eventDispatcher
     * @param FormFactoryInterface          $formFactory
     * @param FilterBuilderUpdaterInterface $filterBuilderUpdater
     * @param RequestStack                  $requestStack
     * @param ViewHandlerInterface          $viewHandler
     * @param ParameterBagInterface         $parameterBag
     */
    public function __construct(
        ApiResourceManagerInterface $apiResourceManager,
        EventDispatcherInterface $eventDispatcher,
        FormFactoryInterface $formFactory,
        FilterBuilderUpdaterInterface $filterBuilderUpdater,
        RequestStack $requestStack,
        ViewHandlerInterface $viewHandler,
        ParameterBagInterface $parameterBag
    ) {
        $this->apiResourceManager = $apiResourceManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->filterBuilderUpdater = $filterBuilderUpdater;
        $this->requestStack = $requestStack;
        $this->viewHandler = $viewHandler;
        $this->parameterBag = $parameterBag;
    }

    /**
     * The function returns the class name of entity handled in this controller.
     *
     * @return string
     */
    abstract public function getApiResourceClass(): string;

    /**
     * The function returns the class name of the filter class.
     *
     * @return string
     */
    abstract public function getApiResourceFilterFormClass(): string;

    /**
     * The function returns the class name of the the filter class to update the entity handled in this controller.
     *
     * @return string
     */
    abstract public function getApiResourceFormClass(): string;

    /**
     * @return int
     */
    public function getApiResourceListLimit(): int
    {
        return 20;
    }

    /**
     * @return View
     *
     * @Rest\Get("")
     * @Rest\View(serializerGroups={"list"})
     */
    public function getApiResourcesAction()
    {
        $queryBuilder = $this->createQueryBuilder();
        $form = $this->formFactory->create($this->getApiResourceFilterFormClass());
        $form->submit($this->requestStack->getCurrentRequest()->query->all());

        $orderByField = $this->requestStack->getMasterRequest()->query->get(
            'orderBy',
            $this->getRepository()::getStandardSortField()
        );
        $orderByDirection = $this->requestStack->getMasterRequest()->query->get(
            'order',
            $this->getRepository()::getStandardSortDirection()
        );

        if (
            in_array($orderByField, $this->getRepository()::getSortableFields())
            && in_array(strtolower($orderByDirection), ['asc', 'desc'])
        ) {
            $queryBuilder->orderBy($queryBuilder->getRootAliases()[0] . '.' . $orderByField, $orderByDirection);
        }

        $this->filterBuilderUpdater->addFilterConditions($form, $queryBuilder);

        $paginationData = $this->paginate($queryBuilder);

        if($this->parameterBag->get('hessnatur_simple_rest_crud.extend_with_query')) {
            $paginationData['query'] = $queryBuilder->getQuery()->getSQL();
        }

        if($this->parameterBag->get('hessnatur_simple_rest_crud.extend_with_filter')) {
            $paginationData['filter'] = $form->getData();
        }

        return View::create($paginationData);
    }

    /**
     * @param string $id
     *
     * @return View
     *
     * @Rest\Get("/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function getApiResourceAction(string $id)
    {
        return View::create($this->fetchApiResource($id));
    }

    /**
     * @param string $id
     *
     * @return View
     *
     * @Rest\Delete("/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function deleteApiResourceAction(string $id)
    {
        $apiResource = $this->fetchApiResource($id);
        if (!$apiResource->getUserCanDelete()) {
            throw new AccessDeniedHttpException();
        }

        $this->eventDispatcher->dispatch(
            new ApiResourceEvent($apiResource),
            HessnaturSimpleRestCRUDEvents::BEFORE_DELETE_API_RESOURCE
        );
        $this->apiResourceManager->remove($apiResource);

        return View::create(null, Response::HTTP_OK);
    }

    /**
     * @param string $id
     *
     * @return View
     *
     * @throws \Exception
     *
     * @Rest\Put("/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function putApiResourceAction(string $id)
    {
        $apiResource = $this->fetchApiResource($id);
        if (!$apiResource->getUserCanUpdate()) {
            throw new AccessDeniedHttpException();
        }

        return $this->postApiResourceAction($apiResource);
    }

    /**
     * @param ApiResource|null $apiResource
     *
     * @return View
     *
     * @throws \Exception
     *
     * @Rest\Post("")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function postApiResourceAction(?ApiResource $apiResource)
    {
        $responseCode = Response::HTTP_OK;
        if ($apiResource === null) {
            $responseCode = Response::HTTP_CREATED;
            $apiResource = $this->createApiResource();

            $this->eventDispatcher->dispatch(
                new ApiResourceEvent($apiResource),
                HessnaturSimpleRestCRUDEvents::AFTER_INSTANTIATE_API_RESOURCE
            );

            if (!$apiResource->getUserCanCreate()) {
                throw new AccessDeniedHttpException();
            }
        }

        $form = $this->formFactory->create($this->getApiResourceFormClass(), $apiResource);
        $form->submit($this->requestStack->getMasterRequest()->request->all());

        if ($form->isValid()) {
            $this->eventDispatcher->dispatch(
                new ApiResourceEvent($apiResource),
                $responseCode === Response::HTTP_CREATED
                    ? HessnaturSimpleRestCRUDEvents::BEFORE_CREATE_API_RESOURCE
                    : HessnaturSimpleRestCRUDEvents::BEFORE_UPDATE_API_RESOURCE
            );
            $this->apiResourceManager->update($apiResource);
            $this->eventDispatcher->dispatch(
                new ApiResourceEvent($apiResource),
                $responseCode === Response::HTTP_CREATED
                    ? HessnaturSimpleRestCRUDEvents::AFTER_CREATE_API_RESOURCE
                    : HessnaturSimpleRestCRUDEvents::AFTER_UPDATE_API_RESOURCE
            );

            return View::create($apiResource, $responseCode);
        }

        return View::create(['form' => $form], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @return ApiResource
     */
    protected function createApiResource()
    {
        $apiResourceClass = $this->getApiResourceClass();

        return new $apiResourceClass();
    }

    /**
     * @param string $id
     *
     * @return ApiResource|object
     */
    protected function fetchApiResource(string $id)
    {
        $repository = $this->apiResourceManager->getRepository($this->getApiResourceClass());
        $apiResource = $repository->findOneBy(['id' => $id]);
        $apiClassName = $this->getApiResourceClass();
        if (
            null === $apiResource
            || !$apiResource instanceof $apiClassName
        ) {
            throw new NotFoundHttpException();
        }

        return $apiResource;
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return array
     */
    protected function paginate(QueryBuilder $queryBuilder)
    {
        $page = intval($this->requestStack->getCurrentRequest()->get('page', 1));
        if ($page === 0) {
            $page = 1;
        }

        $limit = intval($this->requestStack->getCurrentRequest()->get('limit', $this->getApiResourceListLimit()));
        if ($limit === 0 || $limit > $this->getApiResourceListLimit()) {
            $limit = $this->getApiResourceListLimit();
        }

        $results = $queryBuilder->getQuery()->getResult();
        $paginationData = [
            'limit' => $limit,
            'maxResults' => count($results),
            'results' => array_slice($results, ($page - 1) * $limit, $limit),
            'pages' => ceil(count($results) / $limit),
            'currentPage' => $page,
        ];

        return $paginationData;
    }

    /**
     * @param string|null $alias
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder(?string $alias = null)
    {
        if ($alias === null) {
            $alias = 'e';
        }

        return $this->getRepository()->createQueryBuilder($alias);
    }

    /**
     * @return ApiResourceRepositoryInterface
     */
    protected function getRepository()
    {
        $repository = $this->apiResourceManager->getRepository($this->getApiResourceClass());
        if (!$repository instanceof ApiResourceRepositoryInterface) {
            throw new \LogicException(
                sprintf(
                    'You need to use repository %s to use %s, %s given',
                    ApiResourceRepositoryInterface::class,
                    __CLASS__,
                    get_class($repository)
                )
            );
        }

        return $repository;
    }
}
