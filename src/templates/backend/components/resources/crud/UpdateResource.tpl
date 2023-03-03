<?php

namespace backend\components\resources\crud;

use yii\base\Model;
use {{BaseResourceClassName}} as BaseResourceClass;

/**
 * Base file for `UpdateResource`
 */
class UpdateResource extends BaseResourceClass
{
    /** @var string  */
    public string $title;

    /** @var Model */
    public Model $item;
}