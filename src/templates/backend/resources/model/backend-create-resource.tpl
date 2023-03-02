
<?php

namespace backend\resources\{{snake_case}};

use \backend\models\forms\{{CamelCase}}Form;
use \backend\components\resources\crud\CreateResource;

/**
 *
 */
class {{CamelCase}}CreateResource extends CreateResource
{
    /** @var {{CamelCase}}Form */
    public {{CamelCase}}Form $item;
}