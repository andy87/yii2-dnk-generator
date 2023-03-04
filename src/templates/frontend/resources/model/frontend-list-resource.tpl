<?php

namespace frontend\resources\{{snake_case}};

use frontend\models\items\{{CamelCase}};
use andy87\dnk\common\components\resourc\crud\ListResource;

/**
 * List Resource for model `{{CamelCase}}` environment `frontend`
 */
class {{CamelCase}}ListResource extends ListResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-list';
}