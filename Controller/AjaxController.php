<?php

namespace Fp\JsFormValidatorBundle\Controller;

use Doctrine\Common\Util\ClassUtils;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Fp\JsFormValidatorBundle\Utils\EntityPropertyValidator;

/**
 * These actions call from the client side to check some validations on the server side
 * Class AjaxController
 *
 * @package Fp\JsFormValidatorBundle\Controller
 */
class AjaxController extends Controller
{
    /**
     * This is simplified analog for the UniqueEntity validator
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return JsonResponse
     */
    public function checkUniqueEntityAction(Request $request)
    {
        $data = $request->request->all();
        $result = $request->get('checkFromValidator')
            ? $this->validateFromValidator($data)
            : $this->validateFromDB($data);

        return new JsonResponse($result);
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function validateFromDB(array $data)
    {
        foreach ($data['data'] as $value) {
            // If field(s) has an empty value and it should be ignored
            if ((bool) $data['ignoreNull'] && ('' === $value || is_null($value))) {
                return true;
            }
        }
        $entities = $this->getDoctrine()->getRepository($data['entityName'])->{$data['repositoryMethod']}($data['data']);
        $result = empty($entities);
        if (!$result && isset($data['idValues']) && is_array($data['idValues']) && count($entities) === 1) {
            $entity = $entities[0];
            $em = $this->getDoctrine()->getManager();
            $idsValues = $em->getClassMetadata(ClassUtils::getClass($entity))->getIdentifierValues($entity);
            $result = count(array_intersect_assoc($idsValues, $data['idValues'])) === count($idsValues);
        }

        return $result;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    private function validateFromValidator(array $data)
    {
        try {
            $entity = new $data['entityName'];
            $propertyAccessor = $this->get('property_accessor');
            foreach ($data['data'] as $entityDataKey => $entityDataValue) {
                $propertyAccessor->setValue($entity, $entityDataKey, $entityDataValue);
            }

            $constrainOptions = [];
            foreach (['fields', 'message', 'repositoryMethod', 'service', 'ignoreNull', 'groups'] as $propertyName) {
                if (array_key_exists($propertyName, $data)) {
                    $constrainOptions[$propertyName] = $data[$propertyName];
                }
            }
            $constrain = new UniqueEntity($constrainOptions);
            $result = $this->get('validator')->validate($entity, [$constrain], $data['groups'])->count() === 0;
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function checkEntityAction(Request $request)
    {
        $data = $request->request->all();
        if (!array_key_exists('data', $data)) {
            return new JsonResponse();
        }
        if (!class_exists($data['entityName'])) {
            return new JsonResponse(false);
        }

        $validator = new EntityPropertyValidator($this->get('validator'), $this->get('property_accessor'));
        foreach ($data['groups'] as $validationGroup) {
            $result = $validator->validate($data['entityName'], $data['data'], $validationGroup);
            if (!$result) {
                return new JsonResponse(false);
            }
        }

        return new JsonResponse(true);
    }
} 