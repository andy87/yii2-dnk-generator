<?php

namespace frontend\resources\{{snake_case}};

use frontend\models\items\{{CamelCase}};
use common\components\resources\crud\ReadResource;

/**
 * View Resource for model `{{CamelCase}}` environment `frontend
 *
 * @property {{CamelCase}} $item
 */
class {{CamelCase}}ReadResource extends ReadResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-read';
}