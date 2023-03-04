<?php

namespace frontend\services;

use common\services\{{CamelCase}}Service as {{CamelCase}}CommonService;
use common\components\interfaces\services\frontend\ServiceFrontendInterface;
use frontend\models\items\{{CamelCase}};
use frontend\models\forms\{{CamelCase}}Form;
use frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use frontend\resources\{{snake_case}}\{{CamelCase}}ReadResource;

/**
 *  Service for model `{{CamelCase}}` environment `frontend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}Form getForm()
 * @method {{CamelCase}}ReadResource|{{CamelCase}}ListResource getResource(int $key)
 */
class {{CamelCase}}Service extends {{CamelCase}}CommonService implement ServiceFrontendInterface
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
     * @return ActiveRecord
     * @throws ReflectionException
     */
    public function getEntity(): ActiveRecord
    {
        return $this->getForm();
    }
}