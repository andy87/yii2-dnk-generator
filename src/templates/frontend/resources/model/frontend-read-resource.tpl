<?php

namespace frontend\resources\{{snake_case}};

use frontend\models\items\{{CamelCase}};

/**
 * View Resource for model `{{CamelCase}}` environment `frontend`
 */
class {{CamelCase}}ReadResource extends {{BaseResourceClassName}}
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';

    /** @var {{CamelCase}} */
    public {{CamelCase}} $item;
}