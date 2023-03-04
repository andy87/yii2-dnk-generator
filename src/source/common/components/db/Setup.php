<?php

namespace andy87\dnk\common\components\db;

use yii\base\Component;

/**
 *  Настройки базы данных
 */
class Setup extends Component
{
    // Константы

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



    // Свойства

    /** @var string Кодировка */
    public string $character = self::CHARACTER;


    /** @var string Сравнение */
    public string $collate = self::COLLATE;

    /** @var string Сравнение */
    public string $engine = self::ENGINE;

    public array $columns = [
        'created_at' => self::COLUMN['created_at'],
        'updated_at' => self::COLUMN['updated_at'],
    ];
}