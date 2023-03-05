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
     * @return mixed
     */
    public function getClassModel(): string;

    /**
     * @return mixed
     */
    public function getModel(): Model;

    /**
     * @return mixed
     */
    public function create(array $attributes = []): ActiveRecord;

    /**
     * @return mixed
     */
    public function update(ActiveRecord $model, array $attributes = []): ActiveRecord;
}