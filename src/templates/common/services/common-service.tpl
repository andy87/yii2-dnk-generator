<?php

namespace common\services;

use common\models\items\{{CamelCase}};
use {{BaseServiceClassName}} as BaseServiceClass;
use  andy87\dnk\common\components\interfaces\services\common\ServiceCommonInterface;

/**
 *  Service for model `{{CamelCase}}` environment `common`
 *
 * @method {{CamelCase}} getModel()
 */
class {{CamelCase}}Service extends BaseServiceClass implements ServiceCommonInterface
{
    /** @var string */
    public const MODEL = {{CamelCase}}::class;
}