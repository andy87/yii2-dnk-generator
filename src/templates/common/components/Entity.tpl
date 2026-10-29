<?php

namespace common\components;

use common\components\db\Tables;
use yii\base\InvalidConfigException;

/**
 * `Разделы` системы
 */
class Entity
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


    /**
     * Возвращает название раздела во множественном числе.
     *
     * @param int|string $key Идентификатор раздела из констант Entity.
     * @return string Название раздела или имя зарегистрированной таблицы.
     * @throws InvalidConfigException Если раздел не зарегистрирован или название некорректно.
     */
    public static function getLabelMany( int|string $key ): string
    {
        return self::getLabel($key, self::NAME_MANY);
    }

    /**
     * Возвращает название раздела в единственном числе.
     *
     * @param int|string $key Идентификатор раздела из констант Entity.
     * @return string Название раздела или имя зарегистрированной таблицы.
     * @throws InvalidConfigException Если раздел не зарегистрирован или название некорректно.
     */
    public static function getLabelOne( int|string $key ): string
    {
        return self::getLabel($key, self::NAME_ONE);
    }

    /**
     * Поддерживает регистрацию таблицы без необязательных человекочитаемых названий.
     *
     * @param int|string $key Идентификатор раздела.
     * @param int $number Ключ названия в единственном или множественном числе.
     * @return string Настроенное название либо имя таблицы.
     * @throws InvalidConfigException Если название невозможно определить.
     */
    private static function getLabel(int|string $key, int $number): string
    {
        $label = self::DATA[$key][self::LABELS][$number] ?? Tables::NAMES[$key] ?? null;
        if (!is_string($label)) {
            throw new InvalidConfigException("Entity '$key' requires a table in Tables::NAMES or a string label in Entity::DATA.");
        }

        return $label;
    }

}
