<?php

/*
 * (c) hessnatur Textilien GmbH <https://hessnatur.io/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hessnatur\SimpleRestCRUDBundle\Serializer;

use JMS\Serializer\Handler\FormErrorHandler as JMSFormErrorsHandler;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The class renders a simpler error view for form-errors.
 *
 * @author Felix Niedballa <schreib@felixniedballa.de>
 */
class FormErrorHandler extends JMSFormErrorsHandler
{
    /**
     * @var TranslatorInterface
     */
    private $translation;

    public function __construct()
    {
        parent::__construct(null);
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Form                     $form
     * @param array                    $type
     *
     * @return \ArrayObject
     */
    public function serializeFormToJson(JsonSerializationVisitor $visitor, Form $form, array $type)
    {
        return $this->convertToArray($visitor, $form);
    }

    /**
     * @param JsonSerializationVisitor $visitor
     * @param Form                     $data
     *
     * @return \ArrayObject
     */
    private function convertToArray(JsonSerializationVisitor $visitor, Form $data)
    {
        $isRoot = null === $visitor->getRoot();

        $form = new \ArrayObject();

        $errors = [];

        foreach ($data->getErrors() as $error) {
            $errors[] = $this->getMessageError($error);
        }

        if ($errors) {
            $form[] = $errors;
        }

        $children = [];
        foreach ($data->all() as $child) {
            if ($child instanceof Form) {
                if (isset($this->convertToArray($visitor, $child)[0])) {
                    $children[$child->getName()] = $this->convertToArray($visitor, $child)[0];
                }
            }
        }

        if ($children) {
            $form['errors'] = $children;
        }

        if ($isRoot) {
            $visitor->setRoot($children);
        }

        if ($data->getRoot() === $data) {
            return new \ArrayObject($form['errors']);
        }

        return $form;
    }

    /**
     * @param FormError $error
     *
     * @return string
     */
    private function getMessageError(FormError $error)
    {
        if ($this->translation === null) {
            return $error->getMessage();
        }

        if (null !== $error->getMessagePluralization()) {
            return $this->translation->transChoice(
                $error->getMessageTemplate(), $error->getMessagePluralization(), $error->getMessageParameters(),
                'validators'
            );
        }

        return $this->translation->trans($error->getMessageTemplate(), $error->getMessageParameters(), 'validators');
    }
}
