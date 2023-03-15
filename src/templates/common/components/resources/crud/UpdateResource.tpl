<?php

namespace common\components\resources\crud;

use {{BaseResourceClassName}} as BaseResourceClass;
use andy87\dnk\source\interfaces\models\common\ModelInterface;
use andy87\dnk\source\interfaces\resources\crud\UpdateResourcesInterface;

/**
 * Base file for `UpdateResource`
 */
class UpdateResource extends BaseResourceClass implements UpdateResourcesInterface
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