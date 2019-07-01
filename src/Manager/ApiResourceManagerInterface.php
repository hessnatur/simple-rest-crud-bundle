<?php

namespace Hessnatur\SimpleRestCRUDBundle\Manager;

use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
interface ApiResourceManagerInterface
{
    /**
     * @param ApiResource $apiResource
     */
    public function update(ApiResource $apiResource) :void;

    /**
     * @param ApiResource $apiResource
     */
    public function remove(ApiResource $apiResource) :void;
}