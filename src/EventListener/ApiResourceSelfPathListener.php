<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiResourceSelfPathListener implements EventSubscriber
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var string
     */
    private $apiPrefix;

    /**
     * @param RequestStack $requestStack
     * @param string       $apiPrefix
     */
    public function __construct(RequestStack $requestStack, string $apiPrefix)
    {
        $this->requestStack = $requestStack;
        $this->apiPrefix = $apiPrefix;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::postPersist => 'postPersist',
            Events::postLoad => 'postLoad',
        ];
    }

    public function postPersist(LifecycleEventArgs $lifecycleEventArgs)
    {
        $this->setSelfPath($lifecycleEventArgs);
    }

    public function postLoad(LifecycleEventArgs $lifecycleEventArgs)
    {
        $this->setSelfPath($lifecycleEventArgs);
    }

    /**
     * @param LifecycleEventArgs $lifecycleEventArgs
     */
    public function setSelfPath(LifecycleEventArgs $lifecycleEventArgs)
    {
        if (
            ($entity = $lifecycleEventArgs->getEntity()) instanceof ApiResource
            && null !== $this->requestStack->getCurrentRequest()
        ) {
            $lifecycleEventArgs->getEntity()->setSelf(
                str_replace('//', '/', implode(
                    '/', [
                    $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost(),
                    $this->apiPrefix,
                    $entity::getBaseApiPath(),
                    $entity->getId(),
                ]))
            );
        }
    }
}
