<?php

use yii\base\View;
use yii\grid\GridView;
use backend\resources\{{snake_case}}\{{CamelCase}}CreateResource;

/** @var View $this **/
/** @var {{CamelCase}}CreateResource $R **/

?>

<?= $this->render('_form-{{snake_case}}', $R->release()); ?>