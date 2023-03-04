<?php

namespace common\components\interfaces\services\common;

/**
 * Интерфейс сервиса `common` генерируемого библиотекой
 */
interface ServiceCommonInterface
{
    /**
     * @return mixed
     */
    public function getClassModel();

    /**
     * @return mixed
     */
    public function getModel();

    /**
     * @return mixed
     */
    public function create();

    /**
     * @return mixed
     */
    public function update();
}