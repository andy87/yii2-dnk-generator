<?php

namespace common\services;

use common\models\items\{{CamelCase}};
use {{BaseServiceClassName}} as BaseServiceClass;
use common\components\interfaces\services\common\ServiceCommonInterface;

/**
 *  Service for model `{{CamelCase}}` environment `common`
 *
 * @method {{CamelCase}} getModel()
 */
class {{CamelCase}}Service extends BaseServiceClass implement ServiceCommonInterface
{
    /** @var string */
    public const MODEL = {{CamelCase}}::class;
}