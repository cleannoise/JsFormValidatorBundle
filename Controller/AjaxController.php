<?php

namespace Fp\JsFormValidatorBundle\Controller;

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
        foreach ($data['data'] as $value) {
            // If field(s) has an empty value and it should be ignored
            if ((bool) $data['ignoreNull'] && ('' === $value || is_null($value))) {
                // Just return a positive result
                return new JsonResponse(true);
            }
        }

        $entity = $this
            ->get('doctrine')
            ->getRepository($data['entityName'])
            ->{$data['repositoryMethod']}($data['data'])
        ;

        return new JsonResponse(empty($entity));
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

        $validator = new EntityPropertyValidator($this->get('validator'));
        foreach ($data['groups'] as $validationGroup) {
            $result = $validator->validate($data['entityName'], $data['data'], $validationGroup);
            if (!$result) {
                return new JsonResponse(false);
            }
        }

        return new JsonResponse(true);
    }
} 