<?php

namespace andy87\dnk\common\components\core;

use yii\db\ActiveRecord;
use  andy87\dnk\common\components\interfaces\models\common\BaseModelInterface;

/**
 *  Базовый клас для моделей.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими моделями
 */
abstract class BaseModel extends ActiveRecord implements BaseModelInterface
{
    // code ...
}