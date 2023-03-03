<?php

namespace backend\components\resources\crud;

use common\components\resources\GredViewResource;
use {{BaseResourceClassName}} as BaseResourceClass;

/**
 * Base file for `ListResource`
 */
class ListResource extends BaseResourceClass
{
    /** @var string  */
    public string $title;

    /** @var GredViewResource */
    public GredViewResource $gredViewResource;

    /**
     *
     */
    public function __construct( GredViewResource $gredViewResource )
    {
        parent::__construct();

        $this->gredViewResource = $gredViewResource;
    }
}