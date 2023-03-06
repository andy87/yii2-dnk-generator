<?php

namespace common\components\db;

use yii\base\Component;
use common\components\Entity;

/**
 *  Таблицы в системе
 */
class Tables extends Component
{
    /** @var array Список имён таблиц для `разделов` */
    public const NAMES = [
        Entity::USER => 'user',
    ];
}