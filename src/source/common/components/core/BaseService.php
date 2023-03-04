<?php

namespace common\components\core;

use yii\base\Model;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *  Базовый клас для сервисов.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими сервисами
 */
abstract class BaseService extends Component
{
    // константы

    /** @var int Ключ для массива ресурсов */
    public const CREATE = 1;
    /** @var int Ключ для массива ресурсов */
    public const READ = 2;
    /** @var int Ключ для массива ресурсов */
    public const UPDATE = 3;
    /** @var int Ключ для массива ресурсов */
    public const LIST = 4;


    /** @var string Имя класса Модели */
    private const MODEL = Model::class;
    /** @var string Имя класса Формы */
    private const FORM = Model::class;


    /** @var array Массив ресурсов */
    public const RESOURCES = [
        self::LIST => null,
        self::CREATE => null,
        self::UPDATE => null,
        self::READ => null,
    ];



    // методы

    /**
     * Возвращает полное имя класса модели, с которой работает сервис
     */ 
    public function getClassModel(): string
    {
        return self::MODEL;
    }

    /**
     * Возвращает полное имя класса формы, с которой работает сервис
     */
    public function getClassForm(): string
    {
        return self::FORM;
    }

    /**
     * Возвращает экземпляр класса модели, с которой работает сервис
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->createClass(
            $this->getClassForm()
        );
    }

    /**
     * Возвращает экземпляр класса формы, с которой работает сервис
     */
    public function getForm()
    {
        return $this->createClass(
            $this->getClassForm()
        );
    }

    /**
     * Возвращает экземпляр класса ресурса
     *
     * @param int $key
     * @return ?object
     */
    public function getResource( int $key ): ?object
    {
        $class = static::RESOURCES[$key] ?? false;

        if ( $class ) return $this->createClass($class);

        return null;
    }

    /**
     * Возвращает экземпляр класса, переданного в аргументе `$class`
     *
     * @param string $class Имя класса, экземпляр которого будет создан
     * @return mixed
     */
    private function createClass(string $class)
    {
        return new $class();
    }



    /**
     * Создание сущности/модели
     *
     * @param array $attributes
     * @return Model
     */
    public function create( array $attributes = [] ): Model
    {
        $model = $this->getModel();

        if ( !empty($attributes) ) $model->setAttributes($attributes);

        $model->save();

        return $model;
    }

    /**
     * Сохранение сущности/модели
     *
     * @param ActiveRecord $model
     * @param array $attributes
     * @return ActiveRecord
     */
    public function update( ActiveRecord $model, array $attributes = [] ): ActiveRecord
    {
        if ( !empty($attributes) ) $model->setAttributes($attributes);

        $model->save();

        return $model;
    }

    /**
     * Поиск модели
     *
     * @param array $criteria
     * @return ActiveQuery
     */
    public function findWhere( array $criteria ): ActiveQuery
    {
        /** @var ActiveRecord $class */
        $class = static::MODEL;

        return $class::find()->where($criteria);
    }
}