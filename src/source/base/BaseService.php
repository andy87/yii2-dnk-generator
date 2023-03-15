<?php

namespace andy87\dnk\source\base;

use Yii;
use yii\base\{Model,Component,InvalidConfigException};
use yii\db\{ActiveQuery,ActiveRecord,ActiveRecordInterface};
use andy87\dnk\source\interfaces\models\common\ModelInterface;
use andy87\dnk\source\interfaces\services\common\ServiceCommonInterface;

/**
 *  Базовый клас для сервисов.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими сервисами
 */
abstract class BaseService extends Component implements ServiceCommonInterface
{
    // константы

    /** @var int Key for use array resource */
    public const  CREATE = 1;
    /** @var int Key for use array resource */
    public const READ = 2;
    /** @var int Key for use array resource */
    public const UPDATE = 3;
    /** @var int Key for use array resource */
    public const LIST = 4;
    /** @var int Key for use array resource */
    public const FORM = 5;


    /** @var string Имя класса Модели ActiveRecord */
    public const MODEL = Model::class;
    /** @var string Имя класса Формы ActiveRecord */
    public const MODEL_FORM = Model::class;


    /** @var array Массив ресурсов */
    public const RESOURCES = [
        self::LIST => null,
        self::CREATE => null,
        self::UPDATE => null,
        self::READ => null,
    ];



    // Methods

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
        return static::MODEL_FORM;
    }

    /**
     * Возвращает экземпляр класса модели, с которой работает сервис
     *
     * @param array $params
     * @return ModelInterface
     * @throws InvalidConfigException
     */
    public function getModel(array $params = []): ModelInterface
    {
        /** @var ModelInterface $model */
        $model = Yii::createObject($this->getClassModel(), $params);

        return $model;
    }

    /**
     * Возвращает экземпляр класса формы, с которой работает сервис
     *
     * array $params
     * @throws InvalidConfigException
     */
    public function getForm(array $params = []): ModelInterface
    {
        /** @var ModelInterface $model */
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
            return Yii::createObject($class, $params);

        return null;
    }

    /**
     * @return ModelInterface
     * @throws InvalidConfigException
     */
    public function getEntity(): ModelInterface
    {
        return $this->getModel();
    }

    /**
     * Создание сущности/модели
     *
     * @param array $params
     * @param bool $is_save
     * @return ModelInterface
     * @throws InvalidConfigException
     */
    public function create(array $params = [], bool $is_save = true): ModelInterface
    {
        $model = $this->getEntity();

        $model = $this->modelSetAttributes($model, $params);

        return $this->modelSave($model, $is_save);
    }

    /**
     * Сохранение сущности/модели
     *
     * @param ModelInterface $model
     * @param array $params
     * @param bool $is_save
     * @return ModelInterface
     */
    public function update(ModelInterface $model, array $params = [], bool $is_save = true): ModelInterface
    {
        $model = $this->modelSetAttributes($model, $params);

        return $this->modelSave($model, $is_save);
    }

    /**
     * @param ModelInterface $model
     * @param array $params
     * @return ModelInterface
     */
    private function modelSetAttributes(ModelInterface $model, array $params = []): ModelInterface
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
     * @param ModelInterface $model
     * @param bool $is_save
     * @return ModelInterface
     */
    private function modelSave(ModelInterface $model, bool $is_save = true): ModelInterface
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