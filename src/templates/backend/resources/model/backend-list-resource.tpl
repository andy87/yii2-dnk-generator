<?php

namespace backend\resources\{{snake_case}};

use backend\components\resources\crud\ListResource;

/**
 *
 */
class {{CamelCase}}ListResource extends ListResource
{
    /** @var string template name */
    public const TEMPLATE = '{{snake_case}}-list';

    /** @var {{CamelCase}}[] */
    public {{CamelCase}} $items;
}