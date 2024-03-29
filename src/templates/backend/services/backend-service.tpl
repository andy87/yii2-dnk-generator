<?php

namespace backend\services;

use Yii;
use yii\base\InvalidConfigException;
use common\services\{{CamelCase}}Service as {{CamelCase}}CommonService;
use andy87\dnk\source\interfaces\services\backend\ServiceBackendInterface;
use backend\models\items\{{CamelCase}};
use backend\models\forms\{{CamelCase}}Form;
use backend\resources\{{snake_case}}\{
    {{CamelCase}}CreateResource,
    {{CamelCase}}ReadResource,
    {{CamelCase}}UpdateResource,
    {{CamelCase}}ListResource
};

/**
 *  Service for model `{{CamelCase}}` environment `backend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}Form create(array $params = [], bool $is_save = true)
 * @method {{CamelCase}}Form update({{CamelCase}}Form $model, array $params = [], bool $is_save = true)
 * @method {{CamelCase}}CreateResource|{{CamelCase}}ReadResource|{{CamelCase}}UpdateResource|{{CamelCase}}ListResource getResource(int $key, array $arr = [])
 */
class {{CamelCase}}Service extends {{CamelCase}}CommonService implements ServiceBackendInterface
{
    /** @var string  */
    public const MODEL = {{CamelCase}}::class;

    /** @var string  */
    public const MODEL_FORM = {{CamelCase}}Form::class;

    /** @var array  */
    public const RESOURCES = [
        self::CREATE => {{CamelCase}}CreateResource::class,
        self::READ => {{CamelCase}}ReadResource::class,
        self::UPDATE => {{CamelCase}}UpdateResource::class,
        self::LIST => {{CamelCase}}ListResource::class,
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
     * @return {{CamelCase}}Form
     * @throws InvalidConfigException
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
        return static::MODEL_FORM;
    }
}