<?php

use yii\base\View;
use yii\grid\GridView;
use backend\resources\{{snake_case}}\{{CamelCase}}ListResource;

/** @var View $this **/
/** @var {{CamelCase}}ListResource $R **/

?>

<?= GridView::widget([
    'dataProvider' => $R->gridViewResource->activeDataProvider,
    'columns' => [
        [
            'class' => 'yii\grid\SerialColumn'
        ],
        'id',
        'parent_id',
        'name:ntext',
        'url:ntext',
        'category_image:ntext',
        // 'created_at',
        // 'updated_at',
        [
            'class' => 'yii\grid\ActionColumn'
        ],
    ],
]); ?>