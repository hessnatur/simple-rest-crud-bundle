<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\View\View;
use Hessnatur\CSRSupplierSurvey\Manager\ApiResourceManager;
use Hessnatur\CSRSupplierSurvey\Model\ApiResource;
use FOS\RestBundle\Controller\Annotations as Rest;
use Hessnatur\CSRSupplierSurvey\Repository\ApiResourceRepositoryInterface;
use Hessnatur\CSRSupplierSurvey\Service\Authentication;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
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
     * @var ApiResourceManager
     */
    protected $apiResourceManager;

    /**
     * @var Authentication
     */
    protected $authentication;

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
     * @var string
     */
    protected $environment;

    /**
     * @param ApiResourceManager            $apiResourceManager
     * @param Authentication                $authentication
     * @param FormFactoryInterface          $formFactory
     * @param FilterBuilderUpdaterInterface $filterBuilderUpdater
     * @param RequestStack                  $requestStack
     * @param string                        $environment
     */
    public function __construct(
        ApiResourceManager $apiResourceManager,
        Authentication $authentication,
        FormFactoryInterface $formFactory,
        FilterBuilderUpdaterInterface $filterBuilderUpdater,
        RequestStack $requestStack,
        string $environment
    ) {
        $this->apiResourceManager = $apiResourceManager;
        $this->authentication = $authentication;
        $this->formFactory = $formFactory;
        $this->filterBuilderUpdater = $filterBuilderUpdater;
        $this->requestStack = $requestStack;
        $this->environment = $environment;
    }

    /**
     * The function returns the class name of entity handled in this controller.
     *
     * @return string
     */
    public abstract function getApiResourceClass(): string;

    /**
     * The function 
     *
     * @return string
     */
    public abstract function getApiResourceFilterFormClass(): string;

    public abstract function getApiResourceFormClass(): string;

    public function getApiResourceListLimit(): int
    {
        return 20;
    }

    /**
     * @return View
     *
     * @Rest\Get("")
     */
    public function getApiResourcesAction()
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder(strtolower('e'));
        $form = $this->formFactory->create($this->getApiResourceFilterFormClass());
        $form->submit($this->requestStack->getCurrentRequest()->query->all());

        $orderByField = $this->requestStack->getMasterRequest()->query->get(
            'orderBy',
            $this->getRepository()->getStandardSortField()
        );
        $orderByDirection = $this->requestStack->getMasterRequest()->query->get(
            'order',
            $this->getRepository()->getStandardSortDirection()
        );

        if (
            in_array($orderByField, $this->getRepository()->getSortableFields())
            && in_array($orderByDirection, ['ASC', 'DESC'])
        ) {
            $queryBuilder->orderBy($queryBuilder->getRootAliases()[0] . '.' . $orderByField, $orderByDirection);
        }

        $this->filterBuilderUpdater->addFilterConditions($form, $queryBuilder);

        return View::create($this->paginate($queryBuilder));
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
        $apiResource = $this->fetchApiResource($id);
        if (!$apiResource->getUserCanSee()) {
            throw new AccessDeniedHttpException();
        }

        return View::create($apiResource);
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

        $this->apiResourceManager->remove($apiResource);
        return View::create(null, Response::HTTP_OK);
    }

    /**
     * @param string $id
     *
     * @return View
     * @throws \Exception
     *
     * @Rest\Put("/{id}")
     * @Rest\View(serializerGroups={"detail"})
     */
    public function putApiResourceAction(string $id)
    {
        $apiResource = $this->fetchApiResource($id);
        if (!$apiResource->getUserCanEdit()) {
            throw new AccessDeniedHttpException();
        }

        return $this->postApiResourceAction($apiResource);
    }

    /**
     * @param ApiResource|null $apiResource
     *
     * @return View
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

            if (!$apiResource->checkUserCanCreate($this->authentication->getCurrentUser())) {
                throw new AccessDeniedHttpException();
            }
        }

        $form = $this->formFactory->create($this->getApiResourceFormClass(), $apiResource);
        $form->submit($this->requestStack->getMasterRequest()->request->all());

        if ($form->isValid()) {
            $this->apiResourceManager->update($apiResource);

            return View::create($apiResource, $responseCode);
        }

        return View::create(['form' => $form], Response::HTTP_BAD_REQUEST);
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
     * @return ApiResource
     */
    public function createApiResource()
    {
        $apiResourceClass = $this->getApiResourceClass();
        return new $apiResourceClass();
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
            //'query' => $queryBuilder->getQuery()->getSQL(),
        ];

        if ($this->environment === 'dev') {
            $paginationData['query'] = $queryBuilder->getQuery()->getSQL();
        }

        return $paginationData;
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
