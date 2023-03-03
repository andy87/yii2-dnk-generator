<?php

namespace backend\services;

use backend\models\items\{{CamelCase}};
use backend\models\forms\{{CamelCase}}Form;
use backend\resources\{{snake_case}}\{{CamelCase}}CreateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}ViewResource;
use backend\resources\{{snake_case}}\{{CamelCase}}UpdateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}ListResource;

/**
 *  Service for model `{{CamelCase}}` environment `backend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}Form getForm()
 */
class {{CamelCase}}Service extends \common\services\{{CamelCase}}Service
{
    /** @var string  */
    public const MODEL = {{CamelCase}}::class;

    /** @var string  */
    public const FORM = {{CamelCase}}Form::class;

    public const RESOURCES = [
        self::CREATE => {{CamelCase}}CreateResource::class,
        self::VIEW => {{CamelCase}}ReadResource::class,
        self::UPDATE => {{CamelCase}}UpdateResource::class,
        self::LIST => {{CamelCase}}ListResource::class,
    ];
}