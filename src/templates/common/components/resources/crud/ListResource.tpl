<?php

namespace common\components\resources\crud;

use andy87\dnk\source\resources\GridViewResource;
use {{BaseResourceClassName}} as BaseResourceClass;
use andy87\dnk\source\interfaces\resources\crud\ListResourcesInterface;

/**
 * Base file for `ListResource`
 */
class ListResource extends BaseResourceClass implements ListResourcesInterface
{
    /** @var string  */
    public string $title;

    /** @var GridViewResource */
    public GridViewResource $gridViewResource;

    /**
     * @param GridViewResource $gridViewResource
     * @param array $config
     */
    public function __construct( GridViewResource $gridViewResource, array $config = [] )
    {
        $this->gridViewResource = $gridViewResource;

        parent::__construct($config);
    }
}