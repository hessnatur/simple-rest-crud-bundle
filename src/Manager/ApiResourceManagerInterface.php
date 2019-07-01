<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Manager;

use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Hessnatur\SimpleRestCRUDBundle\Repository\ApiResourceRepositoryInterface;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
interface ApiResourceManagerInterface
{
    /**
     * @param ApiResource $apiResource
     */
    public function update(ApiResource $apiResource): void;

    /**
     * @param ApiResource $apiResource
     */
    public function remove(ApiResource $apiResource): void;

    /**
     * @param string $apiResourceClass
     *
     * @return ApiResourceRepositoryInterface
     */
    public function getRepository(string $apiResourceClass): ApiResourceRepositoryInterface;
}
