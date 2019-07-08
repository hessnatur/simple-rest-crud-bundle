HessnaturSimpleRestCRUDBundle
=============

The HessnaturSimpleRestCRUDBundle adds support for creating basic restful CRUD-functionality to the symfony framework. 

Features include:

- expandable filters and forms to handle entities
- expandable ApiResourceModel with UUID and timestamp of creation
- Abstract CRUD-Controller to build endpoints

**Note:** This bundle does *not* provide an authentication system but can
provide the user provider for the core [SecurityBundle](https://symfony.com/doc/current/book/security.html) or [LexikJWTAuthenticationBundle
](https://github.com/lexik/LexikJWTAuthenticationBundle).

Installation
------------

To install the bundle require the bundle via composer and add the main-file to your bundles.php located in the config-directory.

Documentation
-------------

After installation that you can create an entity (inheritance from Hessnatur\SimpleRestCRUDBundle\Model\ApiResource), an update- 
and filter-form (inheritance from Hessnatur\SimpleRestCRUDBundle\Form\ApiResourceType and Hessnatur\SimpleRestCRUDBundle\Filter\ApiResourceFilter).

Now you can create a simple controller like this:

````
// src/Controller/UserController.php

/**
 * @Rest\Route("users")
 */
class UserController extends AbstractApiResourceController
{
    public function getApiResourceClass(): string
    {
        return User::class;
    }

    public function getApiResourceFilterFormClass(): string
    {
        return UserFilterType::class;
    }

    public function getApiResourceFormClass(): string
    {
        return UserType::class;
    }
}
````

The following endpoints a created dynamically:

| Endpoint  | Http Verb | Functionality |
| ------------- | ------------- | ------------- |
| /users/{uuid}  | GET | Returns the user with the given uuid. |
| /users/{uuid}  | PUT | Updates the user with the given uuid (defined in ``getApiResourceFormClass()``). |
| /users  | POST | Creates a new user (defined in ``getApiResourceFormClass()``) |
| /users/{uuid}  | DELETE | DELETES the user with the given uuid. |
| /users  | GET | Lists all users (you can filter with query params defined in ``getApiResourceFilterFormClass()``). |

### Configure paths ###

The bundle sets automatically self paths to the ApiResources. If you prefix this paths, you can configure this in the config file of the bundle:

````
hessnatur_simple_rest_crud:
  settings:
    api_prefix: 'api'
````

### Configure ApiResourceManager ###

You can use an own ApiResourceManager. This class has to implement the ````Hessnatur\SimpleRestCRUDBundle\Manager\ApiResourceManagerInterface````.
In the config file of the bundle you can configure your ApiResourceManager:

````
hessnatur_simple_rest_crud:
  settings:
    api_resource_manager: 'Hessnatur\YourCustomCodeNamespace\Manager\ApiResourceManager'
````

License
-------

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE)

