<?php

namespace common\models\sources;

use {{BaseModelClassName}} as BaseModelClass;
use andy87\dnk\common\components\interfaces\models\common\BaseModelInterface;

/**
 *
 */
class {{CamelCase}}Source extends BaseModelClass implements BaseModelInterface
{
    // call console command:
    // php yii gii/model --modelClass={{CamelCase}}Source --tableName={{snake_case}} --ns=common\\models\\sources --baseClass={{BaseModelClassName}}
}