<?php

namespace backend\components\resources\crud;

use yii\base\Model;
use {{BaseResourceClassName}} as BaseResourceClass;

/**
 * Base file for `ReadResource`
 */
class ReadResource extends BaseResourceClass
{
    public string $title;

    /** @var Model */
    public Model $item;
}