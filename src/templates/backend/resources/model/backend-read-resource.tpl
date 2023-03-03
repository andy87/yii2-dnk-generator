<?php

namespace backend\resources\{{snake_case}};

use backend\models\items\{{CamelCase}};
use backend\components\resources\crud\ReadResource;

/**
 *
 */
class {{CamelCase}}ReadResource extends ReadResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';

    /** @var {{CamelCase}} */
    public {{CamelCase}} $item;
}