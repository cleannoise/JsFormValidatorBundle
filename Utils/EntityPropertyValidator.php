<?php
namespace Fp\JsFormValidatorBundle\Utils;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Class EntityPropertyValidator
 *
 * @package Fp\JsFormValidatorBundle\Utils
 * @author  olsav <olsav@ciklum.com>
 */
class EntityPropertyValidator
{
    /**
     * @var ValidatorInterface
     */
    private $validator;
    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @param ValidatorInterface $validator
     * @param PropertyAccessor   $propertyAccessor
     */
    public function __construct(ValidatorInterface $validator, PropertyAccessor $propertyAccessor)
    {
        $this->validator = $validator;
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * @param string $entityClass
     * @param mixed  $data
     * @param string $validationGroup
     *
     * @return bool
     */
    public function validate($entityClass, $data, $validationGroup)
    {
        $entityMetadata = $this->validator->getMetadataFactory()->getMetadataFor($entityClass);
        $constraints = $entityMetadata->findConstraints($validationGroup);

        $entityConstraints = [];
        foreach ($constraints as $constraint) {
            if (is_array($constraint->fields)) {
                foreach ($constraint->fields as $field) {
                    $entityConstraints[$field] = $constraint;
                }
            } else {
                $entityConstraints[$constraint->fields] = $constraint;
            }
        }

        $entity = new $entityClass;
        foreach ($data as $propertyName => $propertyValue) {
            $this->propertyAccessor->setValue($entity, $propertyName, $propertyValue);
        }

        $errors = $this->validator->validateValue($entity, $entityConstraints, $validationGroup);

        return count($errors) === 0;
    }
}