<?php

namespace common\components\core;

use yii\db\ActiveRecord;
use common\components\interfaces\ModelInterface;

/**
 *  Базовый клас для моделей.
 *      Содержит: константы, свойства и методы, наследуемые всеми рабочими моделями
 */
abstract class BaseModel extends ActiveRecord implements ModelInterface
{
    // code ...
}