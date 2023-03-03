<?php

use yii\base\View;
use yii\grid\GridView;
use backend\resources\{{snake_case}}\{{CamelCase}}UpdateResource;

/** @var View $this **/
/** @var {{CamelCase}}UpdateResource $R **/

?>

<?= $this->render('_form-{{snake_case}}',$R->release()); ?>