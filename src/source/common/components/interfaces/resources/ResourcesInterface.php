<?php

namespace andy87\dnk\source\components\interfaces\resources;

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