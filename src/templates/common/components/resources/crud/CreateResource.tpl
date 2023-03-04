<?php

namespace common\components\resources\crud;

use yii\base\Model;
use {{BaseResourceClassName}} as BaseResourceClass;
use common\components\interfaces\resources\crud\CreateResourcesInterface;

/**
 * Base file for `CreateResource`
 */
class CreateResource extends BaseResourceClass implements CreateResourcesInterface
{
    /** @var string  */
    public string $title;

    /** @var Model */
    public Model $item;

    /**
     * @param Model $item
     * @param array $config
     */
    public function __construct( Model $item, array $config = [] )
    {
        $this->item = $item;

        parent::__construct($config);
    }
}