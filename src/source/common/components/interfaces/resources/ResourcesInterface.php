<?php

namespace common\components\interfaces\resources;

/**
 * Интерфейс общих ресурсов генерируемых библиотекой
 */
interface ResourcesInterface
{
    /**
     * Получение массива для реализации
     *
     * @return mixed
     */
    public function release();
}