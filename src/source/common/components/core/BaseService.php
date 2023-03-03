<?php

namespace common\components\core;

use yii\base\Model;
use yii\base\Component;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 *
 */
abstract class BaseService extends Component
{
    /** @var int keyA to `resources` */
    public const CREATE = 1;
    /** @var int key to `resources` */
    public const READ = 2;
    /** @var int key to `resources` */
    public const UPDATE = 3;
    /** @var int key to `resources` */
    public const LIST = 4;

    /** @var string link to class */
    private const MODEL = Model::class;
    /** @var string link to class */
    private const FORM = Model::class;

    /** @var array  */
    public const RESOURCES = [
        self::LIST => null,
        self::CREATE => null,
        self::UPDATE => null,
        self::READ => null,
    ];


    /**
     * 
     */ 
    public function getClassModel(): string
    {
        return self::MODEL;
    }

    /**
     * 
     */
    public function getClassForm(): string
    {
        return self::FORM;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->createClass(
            $this->getClassForm()
        );
    }

    /**
     * 
     */
    public function getForm()
    {
        return $this->createClass(
            $this->getClassForm()
        );
    }

    /**
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
     * @param string $class
     * @return mixed
     */
    private function createClass(string $class)
    {
        return new $class();
    }



    /**
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