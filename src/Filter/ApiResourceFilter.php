<?php

namespace Hessnatur\SimpleRestCRUDBundle\Filter;

use Hessnatur\SimpleRestCRUDBundle\Model\ApiResource;
use Lexik\Bundle\FormFilterBundle\Filter\Doctrine\ORMQuery;
use Lexik\Bundle\FormFilterBundle\Filter\Form\Type\TextFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
abstract class ApiResourceFilter extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'q',
            TextFilterType::class,
            [
                'mapped' => false,
                'apply_filter' => function (ORMQuery $query, $field, $values) {
                    if (!isset($values['value']) || strlen($values['value']) === 0) {
                        return;
                    }

                    $baseExpr = $query->getExpr();
                    $expr = $query->getExpr()->orX();
                    foreach ($this->getSearchableFields() as $searchableField) {
                        $expr->add(
                            $baseExpr->like(
                                sprintf('%s.%s', $values['alias'], $searchableField),
                                "'%" . $values['value'] . "%'"
                            )
                        );
                    }

                    return $query->createCondition($expr);
                }
            ]
        );
    }

    protected abstract function getSearchableFields(): array;

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
