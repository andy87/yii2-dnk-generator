<?php

namespace andy87\dnk\source\interfaces\services\frontend;

use andy87\dnk\source\interfaces\models\common\ModelFormInterface;

/**
 * Интерфейс сервиса `frontend` генерируемого библиотекой
 */
interface ServiceFrontendInterface
{
    /**
     * @return mixed
     */
    public function getClassForm(): string;

    /**
     * @return mixed
     */
    public function getForm(array $params): ModelFormInterface;

}