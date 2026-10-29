<?php

return [
    'frontend: search model autoloads from the search directory' => function (): void {
        expect(class_exists(frontend\models\search\UserSearch::class), 'Frontend SearchModel cannot be autoloaded.');
        expect(is_file(Yii::getAlias('@frontend/models/search/UserSearch.php')), 'Wrong SearchModel output path.');
        expect(!is_file(Yii::getAlias('@frontend/models/items/UserSearch.php')), 'SearchModel was written to items.');
    },
    'frontend: list renders the filtered DataProvider' => function (): void {
        Yii::$app->db->createCommand()->batchInsert('user', ['name'], [['Visible user'], ['Other user']])->execute();
        Yii::$app->request->setQueryParams(['UserSearch' => ['name' => 'Visible user']]);
        $html = generatedController('frontend')->actionList();
        expect(strpos($html, 'Visible user') !== false, 'The list did not render the matching record.');
        expect(strpos($html, 'Other user') === false, 'The list ignored the query filter.');
        expect(Yii::$app->view->title === 'Пользователи', 'Wrong list title.');
    },
    'frontend: read renders the requested record' => function (): void {
        Yii::$app->db->createCommand()->insert('user', ['name' => 'Visible user'])->execute();
        $id = (int) Yii::$app->db->getLastInsertID();
        $html = generatedController('frontend')->actionRead($id);
        expect(strpos($html, 'detail-view') !== false, 'Record view was not rendered.');
        expect(strpos($html, '>' . $id . '</td>') !== false, 'Wrong record was rendered.');
        expect(Yii::$app->view->title === 'Пользователь: ' . $id . ' view', 'Wrong record title.');
    },
];
