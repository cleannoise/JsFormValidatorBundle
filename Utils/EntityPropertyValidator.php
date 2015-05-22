<?php
namespace Fp\JsFormValidatorBundle\Utils;

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
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
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
        $errors = [];

        foreach ($constraints as $constraint) {
            if (is_array($constraint->fields)) {
                foreach ($constraint->fields as $field) {
                    $entityConstraints[$field] = $constraint;
                }
            } else {
                $entityConstraints[$constraint->fields] = $constraint;
            }
        }

        foreach ($data as $property => $value) {
            if (!array_key_exists($property, $entityConstraints)) {
                continue;
            }

            $constraint = $entityConstraints[$property];
            $constraintErrors = $this->validator->validateValue($value, $constraint, $validationGroup);
            foreach ($constraintErrors as $error) {
                $errors[] = $error;
            }
        }

        return count($errors) === 0;
    }
}