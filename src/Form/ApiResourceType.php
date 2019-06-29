<?php

namespace Hessnatur\SimpleRestCRUDBundle\Form;

use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class ApiResourceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
                'allow_extra_fields' => true,
                'data_class' => ApiResource::class
            ]
        );
    }
}
