<?php

use common\components\Entity;

return [
    'entity: string keys return singular and plural labels' => function (): void {
        expect(Entity::getLabelOne(Entity::USER) === 'Пользователь', 'Wrong singular label.');
        expect(Entity::getLabelMany(Entity::USER) === 'Пользователи', 'Wrong plural label.');
    },
    'entity: backend create saves and completes rendering' => function (): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['UserForm' => [
            'name' => 'Created user', 'description' => 'Valid form', 'status_id' => 1,
        ]]);
        $html = generatedController('backend')->actionCreate();
        expect(strpos($html, 'Created user') !== false, 'Submitted model was not rendered.');
        expect((int) backend\models\items\User::find()->count() === 1, 'Expected one saved record.');
        expect(Yii::$app->view->title === 'Пользователь: create', 'Wrong create title.');
    },
];
