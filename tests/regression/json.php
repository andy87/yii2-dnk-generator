<?php

/** Exercises JSON defaults without an application-specific field list. */
class DefaultJsonController extends frontend\components\controllers\FrontendController
{
    public const SERVICE = frontend\services\UserService::class;
}

/** Exposes an explicitly approved display field in addition to the ID. */
class NamedJsonController extends DefaultJsonController
{
    public const JSON_FIELDS = ['id', 'name'];
}

/** Disables item fields without reverting to Yii's all-fields serialization. */
class EmptyJsonController extends DefaultJsonController
{
    public const JSON_FIELDS = [];
}

/**
 * Checks final JSON bodies against a schema containing private and future fields.
 *
 * @param string $controllerClass Controller with the tested public field list.
 * @param array $expectedFields Exact keys allowed in each returned item.
 * @return void
 * @throws Throwable When generation, serialization or an assertion fails.
 */
function checkPublicJson(string $controllerClass, array $expectedFields): void
{
    $db = Yii::$app->db;
    $tableSql = $db->createCommand("SELECT sql FROM sqlite_master WHERE name='user' AND type='table'")->queryScalar();
    $privateFields = ['password_hash', 'auth_key', 'future_private_field'];
    foreach ($privateFields as $field) $db->createCommand()->addColumn('user', $field, 'TEXT')->execute();
    $db->schema->refreshTableSchema('user');
    try {
        $db->createCommand()->insert('user', array_merge(
            ['name' => 'Public name'], array_fill_keys($privateFields, 'SYNTHETIC_PRIVATE_VALUE')
        ))->execute();
        $id = (int) $db->getLastInsertID();
        $controller = new $controllerClass('user', Yii::$app, ['layout' => false]);
        Yii::$app->controller = $controller;
        Yii::$app->request->setQueryParams(['type' => 'json']);
        foreach (['actionList' => [], 'actionRead' => [$id]] as $method => $args) {
            $response = $controller->$method(...$args);
            $prepare = new ReflectionMethod(yii\web\Response::class, 'prepare');
            if (PHP_VERSION_ID < 80100) $prepare->setAccessible(true);
            $prepare->invoke($response);
            $data = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);
            $item = $method === 'actionList' ? $data['items'][0] : $data['item'];
            expect(array_keys($item) === $expectedFields, 'JSON exposed fields outside the public list.');
            expect(strpos($response->content, 'SYNTHETIC_PRIVATE_VALUE') === false, 'A private value leaked into JSON.');
            if (in_array('id', $expectedFields, true)) expect((int) $item['id'] === $id, 'The public ID was lost.');
            if (in_array('name', $expectedFields, true)) expect($item['name'] === 'Public name', 'The approved field was lost.');
            if ($method === 'actionList') expect($data['totalCount'] === 1, 'The list count changed.');
        }
    } finally {
        Yii::$app->response->format = yii\web\Response::FORMAT_HTML;
        Yii::$app->response->data = null;
        Yii::$app->response->content = null;
        $db->createCommand()->dropTable('user')->execute();
        $db->createCommand($tableSql)->execute();
        $db->schema->refreshTableSchema('user');
    }
}

return [
    'JSON: defaults expose only the ID in list and read' => function (): void {
        checkPublicJson(DefaultJsonController::class, ['id']);
    },
    'JSON: explicitly approved fields are available in list and read' => function (): void {
        checkPublicJson(NamedJsonController::class, ['id', 'name']);
    },
    'JSON: an empty public field list exposes no attributes' => function (): void {
        checkPublicJson(EmptyJsonController::class, []);
    },
];
