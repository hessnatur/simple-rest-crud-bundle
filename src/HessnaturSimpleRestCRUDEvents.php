<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class HessnaturSimpleRestCRUDEvents extends Bundle
{
    /**
     * The event fires after instantiating an api resource.
     */
    public const AFTER_INSTANTIATE_API_RESOURCE = 'hessnatur_simple_rest_crud.after_instantiate_api_resource';

    /**
     * The event fires before creating an api resource.
     */
    public const BEFORE_CREATE_API_RESOURCE = 'hessnatur_simple_rest_crud.before_create_api_resource';

    /**
     * The event fires after creating an api resource.
     */
    public const AFTER_CREATE_API_RESOURCE = 'hessnatur_simple_rest_crud.after_create_api_resource';

    /**
     * The event fires before update an api resource.
     */
    public const BEFORE_UPDATE_API_RESOURCE = 'hessnatur_simple_rest_crud.before_update_api_resource';

    /**
     * The event fires before updating an api resource.
     */
    public const AFTER_UPDATE_API_RESOURCE = 'hessnatur_simple_rest_crud.after_update_api_resource';

    /**
     * The event fires before deleting an api resource.
     */
    public const BEFORE_DELETE_API_RESOURCE = 'hessnatur_simple_rest_crud.before_delete_api_resource';

    /**
     * The event fires after deleting an api resource.
     */
    public const AFTER_DELETE_API_RESOURCE = 'hessnatur_simple_rest_crud.after_delete_api_resource';
}
