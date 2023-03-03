<?php

use yii\base\View;
use yii\widgets\DetailView;
use backend\resources\{{snake_case}}\{{CamelCase}}ReadResource;

/** @var View $this **/
/** @var {{CamelCase}}ReadResource $R **/

?>

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