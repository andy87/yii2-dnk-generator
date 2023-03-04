<?php

namespace common\components;

use common\components\db\Tables;

/**
 * `Разделы` системы
 */
class Part
{
    // Константы

    // Список `разделов`
    /** @var string Пользователи */
    public const USER = 'user';


    // Вспомогательное
    /** @var int Ключ имени в множественном числе */
    public const NAME_MANY = 0;

    /** @var int Ключ имени в единственном числе */
    public const NAME_ONE = 1;

    /** @var int Ключ названий */
    public const LABELS = 1;

    /** @var int Ключ имени таблицы */
    public const TABLE_NAME = 2;


    // Данные `разделов`

    /** @var array */
    public const DATA = [

        self::USER => [
            self::TABLE_NAME => Tables::NAMES[ self::USER ],
            self::LABELS => [
                self::NAME_MANY => 'Пользователи',
                self::NAME_ONE => 'Пользователь'
            ]
        ],
    ];

}