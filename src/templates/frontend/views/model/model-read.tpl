<?php

use yii\base\View;
use yii\widgets\DetailView;
use frontend\resources\{{snake_case}}\{{CamelCase}}ListResource;

/** @var View $this **/
/** @var {{CamelCase}}ListResource $R **/

<?= DetailView::widget([
    'model' => $R->item,
    'attributes' => [
        'id',
        //'description:html',
        //[
        //    'label' => 'Owner',
        //    'value' => $model->owner->name,
        //    'contentOptions' => ['class' => 'bg-red'],
        //    'captionOptions' => ['tooltip' => 'Tooltip'],
        //],
        'created_at:datetime'
    ],
]); ?>