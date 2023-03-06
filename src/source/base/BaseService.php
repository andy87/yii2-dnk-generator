<?php

namespace andy87\dnk\source\base;

use Yii;
use yii\base\{Model,Component,InvalidConfigException};
use yii\db\{ActiveQuery,ActiveRecord,ActiveRecordInterface};

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
    public const MODEL = Model::class;
    /** @var string Имя класса Формы ActiveRecord */
    public const FORM = Model::class;


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
        return static::MODEL;
    }

    /**
     * Возвращает полное имя класса формы, с которой работает сервис
     */
    public function getClassForm(): string
    {
        return static::FORM;
    }

    /**
     * Возвращает экземпляр класса модели, с которой работает сервис
     *
     * @param array $params
     * @return Model
     * @throws InvalidConfigException
     */
    public function getModel(array $params = []): Model
    {
        /** @var Model $model */
        $model = Yii::createObject($this->getClassModel(), $params);

        return $model;
    }

    /**
     * Возвращает экземпляр класса формы, с которой работает сервис
     *
     * array $params
     * @throws InvalidConfigException
     */
    public function getForm(array $params = []): Model
    {
        /** @var Model $model */
        $model = Yii::createObject($this->getClassForm(), $params);

        return $model;
    }

    /**
     * Возвращает экземпляр класса ресурса
     *
     * @param int $key
     * @param array $params
     * @return ?object
     * @throws InvalidConfigException
     */
    public function getResource( int $key, array $params = []): ?object
    {
        if ( ($class = static::RESOURCES[$key] ?? false) )
        {
            return Yii::createObject($class, $params);
        }

        return null;
    }

    /**
     * @return Model
     * @throws InvalidConfigException
     */
    public function getEntity(): Model
    {
        return $this->getModel();
    }

    /**
     * Создание сущности/модели
     *
     * @param array $params
     * @param bool $is_save
     * @return Model
     * @throws InvalidConfigException
     */
    public function create(array $params = [], bool $is_save = true): Model
    {
        $model = $this->getEntity();

        $model = $this->modelSetAttributes($model, $params);

        return $this->modelSave($model, $is_save);
    }

    /**
     * Сохранение сущности/модели
     *
     * @param Model $model
     * @param array $params
     * @param bool $is_save
     * @return Model
     */
    public function update(Model $model, array $params = [], bool $is_save = true): Model
    {
        $model = $this->modelSetAttributes($model, $params);

        return $this->modelSave($model, $is_save);
    }

    /**
     * @param Model $model
     * @param array $params
     * @return Model
     */
    private function modelSetAttributes(Model $model, array $params = []): Model
    {
        if ( !empty($params) )
        {
            $CamelCame = get_class($model);

            $action = ( isset($params[$CamelCame]) ) ? 'load' : 'setAttributes';

            $model->$action($params);
        }

        return $model;
    }

    /**
     * @param Model $model
     * @param bool $is_save
     * @return Model
     */
    private function modelSave(Model $model, bool $is_save = true): Model
    {
        if($is_save && $model instanceof ActiveRecordInterface ) $model->save();

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