<?php

namespace andy87\dnk\source\interfaces\resources;

/**
 * Интерфейс общих ресурсов генерируемых библиотекой
 */
interface ResourcesInterface
{
    /**
     * Получение массива для реализации
     *
     * @return array
     */
    public function release(): array;
}