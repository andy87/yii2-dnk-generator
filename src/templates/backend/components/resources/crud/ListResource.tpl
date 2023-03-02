<?php

namespace backend\components\resources\crud;

use common\components\resources\GredViewResource;

/**
 * Base file for `ListResource`
 */
class ListResource extends {{BaseResourceClassName}}
{
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