<?php

namespace common\components\db;

/**
 *
 */
class Config
{
    /** @var string Кодировка */
    public const CHARACTER = 'utf8mb4';

    /** @var string Сравнение */
    public const COLLATE = 'utf8mb4_unicode_ci';

    /** @var string Движок */
    public const ENGINE = 'InnoDB';

    /** @var array Настройки для колонок */
    public const COLUMN = [
        'created_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
    ];
}