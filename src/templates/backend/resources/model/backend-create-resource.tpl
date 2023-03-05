<?php

namespace backend\resources\{{snake_case}};

use common\components\resources\crud\CreateResource;
use backend\models\forms\{{CamelCase}}Form;

/**
 *  {{CamelCase}}CreateResource
 *
 *  @property {{CamelCase}}Form $item
 */
class {{CamelCase}}CreateResource extends CreateResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-create';
}