<?php

namespace backend\resources\{{snake_case}};

use \backend\models\items\{{CamelCase}};
use \backend\components\resources\crud\ListResource;

/**
 *
 */
class {{CamelCase}}ListResource extends ListResource
{
    /** @var {{CamelCase}}[] */
    public {{CamelCase}} $items;
}