<?php

namespace frontend\services;

use \frontend\models\items\{{CamelCase}};
use \frontend\models\forms\{{CamelCase}}Form;
use \frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use \frontend\resources\{{snake_case}}\{{CamelCase}}ViewResource;

/**
 *  Service for model `{{CamelCase}}` environment `frontend`
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
        self::LIST => {{CamelCase}}ListResource::class,
        self::VIEW => {{CamelCase}}ViewResource::class,
    ];
}