<?php

namespace frontend\resources\{{snake_case}};

use frontend\models\items\{{CamelCase}};
use common\components\resources\GridViewResource;
use {{BaseResourceClassName}} as BaseResourceClass;

/**
 * List Resource for model `{{CamelCase}}` environment `frontend`
 */
class {{CamelCase}}ListResource extends BaseResourceClass
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-list';

    /** @var GridViewResource */
    public GridViewResource $gridViewResource;
}