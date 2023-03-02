<?php

namespace backend\resources\{{snake_case}};

use \backend\models\forms\{{CamelCase}}Form;
use \backend\components\resources\crud\UpdateResource;

/**
 *
 */
class {{CamelCase}}UpdateResource extends UpdateResource
{
    /** @var {{CamelCase}}Form */
    public {{CamelCase}}Form $item;
}