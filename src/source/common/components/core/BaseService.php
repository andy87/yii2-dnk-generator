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
    /** @var int key to `resources` */
    public const LIST = 1;
    /** @var int key to `resources` */
    public const CREATE = 2;
    /** @var int key to `resources` */
    public const UPDATE = 3;
    /** @var int key to `resources` */
    public const VIEW = 4;


    /** @var string link to class */
    private const MODEL = Model::class;
    /** @var string link to class */
    private const FORM = Model::class;



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
        $class = $this->getClassForm();

        return new $class();
    }

    /**
     * 
     */
    public function getForm()
    {
        $class = $this->getClassForm();

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