<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class ApiResourceType extends AbstractType
{
    /**
     * Configure standard options to prevent csrf protection and allow extra fields.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
                'allow_extra_fields' => true,
            ]
        );
    }
}
