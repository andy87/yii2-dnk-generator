<?php

namespace frontend\resources\{{snake_case}};

use \frontend\models\items\{{CamelCase}};

/**
 * List Resource for model `{{CamelCase}}` environment `frontend`
 */
class {{CamelCase}}ListResource extends {{BaseResourceClassName}}
{
    /** @var {{CamelCase}}[] */
    public array $items;
}