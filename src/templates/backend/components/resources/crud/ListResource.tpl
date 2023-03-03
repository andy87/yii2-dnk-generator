<?php

namespace backend\components\resources\crud;

use common\components\resources\GridViewResource;
use {{BaseResourceClassName}} as BaseResourceClass;

/**
 * Base file for `ListResource`
 */
class ListResource extends BaseResourceClass
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