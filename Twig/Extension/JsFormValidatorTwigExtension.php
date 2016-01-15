<?php
namespace Fp\JsFormValidatorBundle\Twig\Extension;

use Fp\JsFormValidatorBundle\Factory\JsFormValidatorFactory;
use Symfony\Component\Form\FormView;

/**
 * Class JsFormValidatorTwigExtension
 *
 * @package Fp\JsFormValidatorBundle\Twig\Extension
 */
class JsFormValidatorTwigExtension extends \Twig_Extension
{
    /**
     * @var JsFormValidatorFactory
     */
    protected $factory;

    /**
     * @param JsFormValidatorFactory $factory
     *
     * @codeCoverageIgnore
     */
    public function __construct(JsFormValidatorFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('init_js_validation', [$this, 'getJsValidator'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('js_validator_config', [$this, 'getConfig'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        return $this->factory->getJsConfigString();
    }

    /**
     * @param null|string|FormView $form
     * @param bool                 $onLoad
     * @param bool                 $wrapped
     *
     * @return string
     */
    public function getJsValidator($form = null, $onLoad = true, $wrapped = true)
    {
        if ($form instanceof FormView) {
            $form = $form->vars['name'];
        }
        $jsModels = $this->factory->getJsValidatorString($form, $onLoad);
        if ($wrapped) {
            $jsModels = '<script type="text/javascript">' . $jsModels . '</script>';
        }

        return $jsModels;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return 'fp_js_form_validator';
    }
}
