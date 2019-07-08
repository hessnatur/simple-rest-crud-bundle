<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Event;

use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @author Felix Niedballa <felix.niedballa@hess-natur.de>
 */
class ApiResourceEvent extends Event
{
    /**
     * @var ApiResource
     */
    private $apiResource;

    /**
     * @param ApiResource $apiResource
     */
    public function __construct(ApiResource $apiResource)
    {
        $this->apiResource = $apiResource;
    }

    /**
     * @return ApiResource
     */
    public function getApiResource()
    {
        return $this->apiResource;
    }
}
