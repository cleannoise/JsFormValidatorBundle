<?php

namespace Fp\JsFormValidatorBundle\Controller;

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
            $result = empty($this->get('validator')->validate($entity, [$constrain], $data['groups']));
        } catch (\Exception $e) {
            $result = false;
        }

        return new JsonResponse($result);
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