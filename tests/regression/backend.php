<?php

return [
    'backend: invalid create renders validation errors without inserting a row' => function (): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['UserForm' => ['name' => '']]);
        $html = generatedController('backend')->actionCreate();
        expect((int) backend\models\items\User::find()->count() === 0, 'Invalid record was inserted.');
        expect(strpos($html, '<div class="help-block">Name cannot be blank.</div>') !== false, 'Validation error was not rendered.');
    },
    'backend: create exposes the saved model and its primary key to the view' => function (): void {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['UserForm' => ['name' => 'Saved record']]);
        $controller = generatedController('backend');
        $resource = null;
        $controller->addMutation(new andy87\dnk\source\resources\MutationResource(
            backend\components\controllers\BackendController::EVENT_RESOURCE_CREATED,
            andy87\dnk\source\base\BaseService::CREATE,
            static function ($created) use (&$resource) {
                $resource = $created;
                return $created;
            }
        ));
        $controller->actionCreate();
        expect(!$resource->item->isNewRecord, 'The view received an unsaved model.');
        expect($resource->item->id !== null, 'The saved primary key was lost.');
        expect(backend\models\items\User::findOne($resource->item->id)->name === 'Saved record', 'The view model does not match the saved record.');
    },
    'backend: generated list renders stored records' => function (): void {
        Yii::$app->db->createCommand()->insert('user', ['name' => 'Visible user'])->execute();
        $html = generatedController('backend')->actionList();
        expect(strpos($html, 'Visible user') !== false, 'The generated list did not render the record.');
        expect(Yii::$app->view->title === 'Пользователи', 'Wrong list title.');
    },
    'backend: search filters by a numeric attribute without required form fields' => function (): void {
        Yii::$app->db->createCommand()->batchInsert('user', ['name'], [['First'], ['Second']])->execute();
        $id = Yii::$app->db->getLastInsertID();
        $search = new backend\models\search\UserSearch();
        $provider = $search->search(['UserSearch' => ['id' => $id]]);
        expect($provider->getTotalCount() === 1, 'ID filter did not narrow the query.');
        expect($provider->getModels()[0]->name === 'Second', 'Wrong row matched the filter.');
        expect(!$search->hasErrors(), 'Model creation rules leaked into search validation.');
    },
    'backend: invalid filters return no rows and expose validation errors' => function (): void {
        Yii::$app->db->createCommand()->insert('user', ['name' => 'Visible user'])->execute();
        foreach ([['id' => 'invalid'], ['name' => ['unexpected']]] as $filter) {
            $search = new backend\models\search\UserSearch();
            $provider = $search->search(['UserSearch' => $filter]);
            expect($provider->getTotalCount() === 0, 'Invalid input exposed the unfiltered list.');
            expect($search->hasErrors(), 'Invalid filter was not validated.');
        }
    },
];
