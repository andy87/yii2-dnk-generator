<?php

namespace frontend\services;

use Yii;
use yii\base\InvalidConfigException;
use common\services\{{CamelCase}}Service as {{CamelCase}}CommonService;
use andy87\dnk\source\interfaces\services\frontend\ServiceFrontendInterface;
use frontend\models\{
    items\{{CamelCase}},
    forms\{{CamelCase}}Form
};
use frontend\resources\{{snake_case}}\{ {{CamelCase}}ListResource, {{CamelCase}}ReadResource };

/**
 *  Service for model `{{CamelCase}}` environment `frontend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}Form create(array $params = [], bool $is_save = true)
 * @method {{CamelCase}}Form update({{CamelCase}}Form $model, array $params = [], bool $is_save = true)
 * @method {{CamelCase}}ReadResource|{{CamelCase}}ListResource getResource(int $key, array $arr = [])
 */
class {{CamelCase}}Service extends {{CamelCase}}CommonService implements ServiceFrontendInterface
{
    /** @var string  */
    public const MODEL = {{CamelCase}}::class;

    /** @var string  */
    public const MODEL_FORM = {{CamelCase}}Form::class;

    /** @var array  */
    public const RESOURCES = [
        self::LIST => {{CamelCase}}ListResource::class,
        self::READ => {{CamelCase}}ReadResource::class,
    ];

    /**
     * @param array $params
     * @return {{CamelCase}}Form
     * @throws InvalidConfigException
     */
    public function getForm(array $params = []): {{CamelCase}}Form
    {
        /** @var {{CamelCase}}Form $model */
        $form = Yii::createObject($this->getClassForm(), $params);

        return $form;
    }

    /**
     * @return string
     */
    public function getClassForm(): string
    {
        return static::MODEL_FORM;
    }
}