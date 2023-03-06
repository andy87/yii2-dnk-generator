<?php

namespace andy87\dnk\source\interfaces\services\common;

use yii\db\ActiveRecord;
use andy87\dnk\source\interfaces\models\common\ModelInterface as CommonModelInterface;
use andy87\dnk\source\interfaces\models\backend\ModelInterface as BackendModelInterface;
use andy87\dnk\source\interfaces\models\frontend\ModelInterface as FrontendModelInterface;

/**
 * Интерфейс сервиса `common` генерируемого библиотекой
 */
interface ServiceCommonInterface
{
    /**
     * @return string
     */
    public function getClassModel(): string;

    /**
     * @return CommonModelInterface|BackendModelInterface|FrontendModelInterface
     */
    public function getModel();

    /**
     * @param array $attributes
     * @return CommonModelInterface|BackendModelInterface|FrontendModelInterface
     */
    public function create(array $attributes = []);

    /**
     * @param ActiveRecord $model
     * @param array $attributes
     * @return CommonModelInterface|BackendModelInterface|FrontendModelInterface
     */
    public function update(ActiveRecord $model, array $attributes = []);
}