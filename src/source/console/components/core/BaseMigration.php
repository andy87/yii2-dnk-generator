<?php

namespace console\components\migrations;

use common\components\db\Tables;
use common\components\Part;
use yii\db\Migration;
use common\components\db\Config;

/**
 * Базовый класс миграций.
 *  Задаёт опции таблиц при миграциях
 */
abstract class BaseMigration extends Migration
{
    // Свойства
    /** @var string Ключ `раздела` в системе */
    public string $part;

    /** @var string Имя таблицы */
    protected string $tableName;

    /** @var string Опции таблицы */
    protected string $tableOptions;



    // Методы

    /**
     * Инициализация
     *
     * @return void
     */
    public function init(): void
    {
        parent::init();

        $this->tableName = Tables::NAMES[ $this->part ];

        $this->tableOptions = sprintf(
            'CHARACTER SET %s COLLATE %s ENGINE=%s',
            Config::CHARACTER,
            Config::COLLATE,
            Config::ENGINE
        );
    }

    /**
     * Сокращённое создание таблицы.
     *
     * Метод должен возвращать стандартный массив данных о таблице
     *
     * @return array
     */
    abstract function constructTable(): array;


    /**
     * Подготовленный метод применения миграции
     *
     * @return void
     */
    public function safeUp(): void
    {
        // Создание таблицы (средствами Yii2)
        parent::createTable($this->tableName, $this->constructTable(), $this->tableOptions );

        //Добавление комментария к таблице (средствами Yii2)
        $tableComment = Part::DATA[ $this->part ]['name'][ Part::NAME_MANY ];
        if ( strlen($tableComment) ) $this->addCommentOnTable($this->tableName, $tableComment );
    }

    /**
     * Подготовленный метод отката миграции
     *
     * @return void
     */
    public function safeDown(): void
    {
        $this->dropTable( $this->tableName );
    }
}