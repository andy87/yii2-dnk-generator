<?php

namespace frontend\services;

use Yii;
use yii\base\Model;
use InvalidConfigException;
use common\services\{{CamelCase}}Service as {{CamelCase}}CommonService;
use andy87\dnk\source\interfaces\services\frontend\ServiceFrontendInterface;
use frontend\models\items\{{CamelCase}};
use frontend\models\forms\{{CamelCase}}Form;
use frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;
use frontend\resources\{{snake_case}}\{{CamelCase}}ReadResource;

/**
 *  Service for model `{{CamelCase}}` environment `frontend`
 *
 * @method {{CamelCase}} getModel()
 * @method {{CamelCase}}Form create(array $params = [], bool $is_save = true)
 * @method {{CamelCase}}Form update(Model $model, array $params = [], bool $is_save = true)
 * @method {{CamelCase}}ReadResource|{{CamelCase}}ListResource getResource(int $key, array $arr = [])
 */
class {{CamelCase}}Service extends {{CamelCase}}CommonService implements ServiceFrontendInterface
{
    /** @var string  */
    public const MODEL = {{CamelCase}}::class;

    /** @var string  */
    public const FORM = {{CamelCase}}Form::class;

    /** @var array  */
    public const RESOURCES = [
        self::LIST => {{CamelCase}}ListResource::class,
        self::READ => {{CamelCase}}ReadResource::class,
    ];

    /**
     * @return Model|{{CamelCase}}Form
     * @throws InvalidConfigException
     */
    public function getForm(): Model|{{CamelCase}}Form
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
        return static::FORM;
    }
}