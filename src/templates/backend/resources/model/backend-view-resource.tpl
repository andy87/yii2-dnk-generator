<?php

namespace backend\resources\{{snake_case}};

use \backend\models\items\{{CamelCase}};
use \backend\components\resources\crud\ViewResource;

/**
 *
 */
class {{CamelCase}}ViewResource extends ViewResource
{
    /** @var {{CamelCase}} */
    public {{CamelCase}} $item;
}