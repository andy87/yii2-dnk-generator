<?php

namespace andy87\dnk\common\components\interfaces\resources;

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