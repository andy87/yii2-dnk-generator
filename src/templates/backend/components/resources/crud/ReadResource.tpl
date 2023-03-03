<?php

namespace backend\components\resources\crud;

use yii\base\Model;

/**
 * Base file for `ReadResource`
 */
class ReadResource extends {{BaseResourceClassName}}
{
    public string $title;

    /** @var Model */
    public Model $item;
}