<?php

namespace common\components\resources\crud;

use yii\base\Model;
use {{BaseResourceClassName}} as BaseResourceClass;
use  andy87\dnk\common\components\interfaces\resources\crud\ReadResourcesInterface;

/**
 * Base file for `ReadResource`
 */
class ReadResource extends BaseResourceClass implements ReadResourcesInterface
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