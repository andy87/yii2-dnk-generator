<?php

namespace common\components\db;

use common\components\Part;

/**
 *  Таблицы в системе
 */
class Tables
{
    /** @var array Список имён таблиц для `разделов` */
    public const NAMES = [
        Part::USER => 'user',
    ];
}