<?php

namespace backend\resources\{{snake_case}};

use common\components\resources\crud\ReadResource;
use backend\models\items\{{CamelCase}}Form;

/**
 *  {{CamelCase}}ReadResource
 *
 *  @property {{CamelCase}}Form $item
 */
class {{CamelCase}}ReadResource extends ReadResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';
}