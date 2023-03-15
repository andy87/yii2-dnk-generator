<?php

namespace common\components\resources\crud;

use {{BaseResourceClassName}} as BaseResourceClass;
use andy87\dnk\source\interfaces\models\common\ModelInterface;
use andy87\dnk\source\interfaces\resources\crud\CreateResourcesInterface;

/**
 * Base file for `CreateResource`
 */
class CreateResource extends BaseResourceClass implements CreateResourcesInterface
{
    /** @var string  */
    public string $title;

    /** @var ModelInterface */
    public ModelInterface $item;

    /**
     * @param ModelInterface $item
     * @param array $config
     */
    public function __construct( ModelInterface $item, array $config = [] )
    {
        $this->item = $item;

        parent::__construct($config);
    }
}