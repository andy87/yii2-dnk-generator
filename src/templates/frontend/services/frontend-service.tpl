<?php

namespace frontend\services;

use ReflectionException;
use common\services\{{CamelCase}}Service as {{CamelCase}}CommonService;
use andy87\dnk\common\components\interfaces\services\frontend\ServiceFrontendInterface;
use frontend\models\items\{{CamelCase}};
use frontend\models\forms\{{CamelCase}}Form;
use frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use frontend\resources\{{snake_case}}\{{CamelCase}}ReadResource;

/**
 *  Service for model `{{CamelCase}}` environment `frontend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}Form getForm()
 * @method {{CamelCase}}ReadResource|{{CamelCase}}ListResource getResource(int $key, array $arr = [])
 */
class {{CamelCase}}Service extends {{CamelCase}}CommonService implements ServiceFrontendInterface
{
    /** @var string  */
    public const MODEL = {{CamelCase}}::class;

    /** @var string  */
    public const FORM = {{CamelCase}}Form::class;

    public const RESOURCES = [
        self::LIST => {{CamelCase}}ListResource::class,
        self::READ => {{CamelCase}}ReadResource::class,
    ];

    /**
     * @return {{CamelCase}}Form
     * @throws ReflectionException
     */
    public function getForm(): {{CamelCase}}Form
    {
        return $this->createClass(
            $this->getClassForm()
        );
    }

    /**
     * @return {{CamelCase}}Form
     * @throws ReflectionException
     */
    public function getEntity(): {{CamelCase}}Form
    {
        return $this->getForm();
    }

    /**
     * @return string
     */
    public function getClassForm(): string
    {
        return static::FORM;
    }
}