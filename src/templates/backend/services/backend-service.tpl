<?php

namespace backend\services;

use ReflectionException;
use common\services\{{CamelCase}}Service as {{CamelCase}}CommonService;
use andy87\dnk\source\components\interfaces\services\backend\ServiceBackendInterface;
use backend\models\items\{{CamelCase}};
use backend\models\forms\{{CamelCase}}Form;
use backend\resources\{{snake_case}}\{{CamelCase}}CreateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}ReadResource;
use backend\resources\{{snake_case}}\{{CamelCase}}UpdateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}ListResource;

/**
 *  Service for model `{{CamelCase}}` environment `backend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}CreateResource|{{CamelCase}}ReadResource|{{CamelCase}}UpdateResource|{{CamelCase}}ListResource getResource(int $key, array $arr = [])
 */
class {{CamelCase}}Service extends {{CamelCase}}CommonService implements ServiceBackendInterface
{
    /** @var string  */
    public const MODEL = {{CamelCase}}::class;

    /** @var string  */
    public const FORM = {{CamelCase}}Form::class;

    public const RESOURCES = [
        self::CREATE => {{CamelCase}}CreateResource::class,
        self::READ => {{CamelCase}}ReadResource::class,
        self::UPDATE => {{CamelCase}}UpdateResource::class,
        self::LIST => {{CamelCase}}ListResource::class,
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