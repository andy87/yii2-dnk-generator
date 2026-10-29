<?php

/**
 * Exercises the base frontend actions without overriding their implementation.
 */
class InheritedFrontendController extends frontend\components\controllers\FrontendController
{
    public const SERVICE = frontend\services\UserService::class;
}

/**
 * Creates the inherited controller with the generated views and a stored record.
 *
 * @return array Controller and primary key of the fixture record.
 * @throws Throwable On fixture or controller initialization failure.
 */
function inheritedFrontend(): array
{
    Yii::$app->db->createCommand()->insert('user', ['name' => 'Inherited record'])->execute();
    Yii::$app->setViewPath(Yii::getAlias('@frontend/views'));
    $controller = new InheritedFrontendController('user', Yii::$app, ['layout' => false]);
    Yii::$app->controller = $controller;
    return [$controller, (int) Yii::$app->db->getLastInsertID()];
}

return [
    'inherited frontend: list renders records from its service query' => function (): void {
        [$controller] = inheritedFrontend();
        expect(strpos($controller->actionList(), 'Inherited record') !== false, 'The inherited list did not render the record.');
    },
    'inherited frontend: read renders an existing record' => function (): void {
        [$controller, $id] = inheritedFrontend();
        expect(strpos($controller->actionRead($id), '<td>' . $id . '</td>') !== false, 'The inherited read did not render the ID.');
    },
    'inherited frontend: JSON list and read produce usable response bodies' => function (): void {
        [$controller, $id] = inheritedFrontend();
        Yii::$app->request->setQueryParams(['type' => 'json']);
        foreach (['actionList' => [], 'actionRead' => [$id]] as $method => $args) {
            try {
                $response = $controller->$method(...$args);
                expect($response instanceof yii\web\Response, 'The action did not return a Response.');
                expect($controller->response instanceof yii\web\Response, 'The Response object was replaced.');
                expect($response->format === yii\web\Response::FORMAT_JSON, 'JSON format was not selected.');
                $prepare = new ReflectionMethod(yii\web\Response::class, 'prepare');
                $prepare->setAccessible(true);
                $prepare->invoke($response);
                $data = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);
                $item = $method === 'actionList' ? $data['items'][0] : $data['item'];
                expect((int) $item['id'] === $id && $item['name'] === 'Inherited record', 'The JSON body lost the record.');
                if ($method === 'actionList') expect($data['totalCount'] === 1, 'The JSON count is incorrect.');
                expect(!isset($data['gridViewResource']), 'The response exposed framework query objects.');
            } finally {
                Yii::$app->response->format = yii\web\Response::FORMAT_HTML;
                Yii::$app->response->data = null;
                Yii::$app->response->content = null;
            }
        }
    },
];
