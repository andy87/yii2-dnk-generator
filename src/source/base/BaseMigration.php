<?php

namespace andy87\dnk\source\base;

use Yii;
use yii\db\Migration;
use yii\base\InvalidConfigException;
use common\components\Entity;
use common\components\db\Setup;
use common\components\db\Tables;

/**
 * Базовый класс миграций.
 *  Задаёт опции таблиц при миграциях
 */
abstract class BaseMigration extends Migration
{
    // Свойства
    /** @var string Ключ `раздела` в системе */
    public string $entity;

    /** @var Setup Настройки базы данных */
    protected Setup $setup;

    /** @var string Имя таблицы */
    protected string $tableName;

    /** @var string Опции таблицы */
    protected string $tableOptions;



    // Методы

    /**
     * Инициализация
     *
     * @return void
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        $this->setSetup();

        $this->tableName = Tables::NAMES[ $this->entity ];

        $this->tableOptions = sprintf(
            'CHARACTER SET %s COLLATE %s ENGINE=%s',
            $this->setup->character,
            $this->setup->collate,
            $this->setup->engine
        );
    }

    /**
     * @return void
     * @throws InvalidConfigException
     */
    private function setSetup(): void
    {
        $setup = Yii::createObject(Setup::class);

        $this->setup = $setup;
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
        $tableComment = Entity::DATA[ $this->entity ][Entity::LABELS][ Entity::NAME_MANY ];
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