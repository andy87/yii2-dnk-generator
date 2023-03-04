<?php

namespace common\components\core;

use ReflectionClass;
use ReflectionException;
use yii\base\Model;
use yii\base\Component;
use yii\base\InvalidArgumentException;
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


    /** @var string Имя класса Модели ActiveRecord */
    private const MODEL = Model::class;
    /** @var string Имя класса Формы ActiveRecord */
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
     *
     * @return ActiveRecord
     * @throws ReflectionException
     */
    public function getModel(): ActiveRecord
    {
        return $this->createClass(
            $this->getClassForm()
        );
    }

    /**
     * Возвращает экземпляр класса формы, с которой работает сервис
     *
     * @throws ReflectionException
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
     * @param array $params
     * @return ?object
     * @throws ReflectionException
     */
    public function getResource( int $key, array $params = []): ?object
    {
        if ( ($class = static::RESOURCES[$key] ?? false) )
        {
            return $this->createClass($class, $params);
        }

        return null;
    }

    /**
     * Возвращает экземпляр класса, переданного в аргументе `$class`
     *
     * @param string $class Имя класса, экземпляр которого будет создан
     * @param array $params Параметры класса
     * @return mixed
     * @throws ReflectionException
     */
    private function createClass(string $class, array $params = []): object
    {
        $reflection = new ReflectionClass($class);

        $constructorParams = $reflection->getConstructor() ? $reflection->getConstructor()->getParameters() : [];

        foreach ($constructorParams as $param)
        {
            if (!array_key_exists($param->name, $params) && !$param->isOptional()) {
                throw new InvalidArgumentException("Missing required parameter: {$param->name}");
            }
        }

        return $reflection->newInstanceArgs($params);
    }

    /**
     * @return ActiveRecord
     * @throws ReflectionException
     */
    public function getEntity(): ActiveRecord
    {
        return $this->getModel();
    }

    /**
     * Создание сущности/модели
     *
     * @param array $attributes
     * @return ActiveRecord
     * @throws ReflectionException
     */
    public function create( array $attributes = [] ): ActiveRecord
    {
        $model = $this->getEntity();

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