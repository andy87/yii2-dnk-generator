<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\db\ExpressionInterface;
use backend\models\forms\{{CamelCase}}Form;
use backend\resources\{{snake_case}}\{{CamelCase}}CreateResource;
use backend\resources\{{snake_case}}\{{CamelCase}}UpdateResource;

/* @var View $this */
/* @var ActiveForm $form */
/* @var {{CamelCase}}Form $item **/
/* @var {{CamelCase}}CreateResource|{{CamelCase}}UpdateResource $R */

$model = $R->item;

?>

<div class="model-class-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php foreach ($model->safeAttributes() as $attribute): ?>
        <?php
        $column = $model->getTableSchema()->getColumn($attribute);
        // Автоматические колонки заполняет БД; виртуальные безопасные поля остаются в форме.
        if ($column !== null && ($column->autoIncrement || $column->defaultValue instanceof ExpressionInterface)) continue;
        // Массивам и объектам нужен специализированный виджет вместо текстового input.
        if (($column !== null && in_array($column->phpType, ['array', 'resource'], true))
            || ($model->$attribute !== null && !is_scalar($model->$attribute))) continue;
        ?>
        <?= $form->field($model, $attribute)->textInput(['maxlength' => true]) ?>
    <?php endforeach; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Добавить' : 'Сохранить', [
            'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
        ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
