<?php

namespace common\components;

use common\components\db\Tables;

/**
 * `Разделы` системы
 */
class Part
{
    // Список `разделов`

    /** @var string Пользователи */
    public const USER = 'user';

    // Имена `разделов`

    /** @var int Ключ имени в множественном числе */
    public const NAME_MANY = 0;

    /** @var int Ключ имени в удинственном числе */
    public const NAME_ONE = 1;



    // Данные `разделов`

    /** @var array  */
    public const DATA = [

        self::USER => [
            'table_name' => Tables::NAMES[ self::USER ],
            'name' => [ 'Пользователи', 'Пользователь' ]
        ],


    ];

}