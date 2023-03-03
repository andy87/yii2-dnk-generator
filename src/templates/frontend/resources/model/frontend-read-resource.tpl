<?php

namespace frontend\resources\{{snake_case}};

use frontend\models\items\{{CamelCase}};
use {{BaseResourceClassName}} as BaseResourceClass;

/**
 * View Resource for model `{{CamelCase}}` environment `frontend
 */
class {{CamelCase}}ReadResource extends BaseResourceClass
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';

    /** @var {{CamelCase}} $item */
    public {{CamelCase}} $item;
}