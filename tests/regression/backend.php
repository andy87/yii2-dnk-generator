<?php

return [
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
