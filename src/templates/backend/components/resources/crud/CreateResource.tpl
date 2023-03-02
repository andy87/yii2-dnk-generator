<?php

namespace backend\components\resources\crud;

use yii\base\Model;

/**
 * Base file for `UpdateResource`
 */
class CreateResource extends {{BaseResourceClassName}}
{
    public string $title;

    /** @var Model */
    public Model $item;
}