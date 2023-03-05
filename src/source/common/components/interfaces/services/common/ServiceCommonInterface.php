<?php

namespace andy87\dnk\common\components\interfaces\services\common;

use yii\base\Model;
use yii\db\ActiveRecord;

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
     * @return Model
     */
    public function getModel(): Model;

    /**
     * @param array $attributes
     * @return ActiveRecord
     */
    public function create(array $attributes = []): ActiveRecord;

    /**
     * @param ActiveRecord $model
     * @param array $attributes
     * @return ActiveRecord
     */
    public function update(ActiveRecord $model, array $attributes = []): ActiveRecord;
}