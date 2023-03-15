<?php

namespace andy87\dnk\source\interfaces\services\common;

use andy87\dnk\source\interfaces\models\common\ModelInterface;

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
     * @return ModelInterface
     */
    public function getModel(): ModelInterface;

    /**
     * @param array $attributes
     * @return ModelInterface
     */
    public function create(array $attributes = []): ModelInterface;

    /**
     * @param ModelInterface $model
     * @param array $attributes
     * @return ModelInterface
     */
    public function update(ModelInterface $model, array $attributes = []): ModelInterface;
}