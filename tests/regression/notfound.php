<?php

/**
 * Covers missing records in the inherited frontend action independently of other groups.
 */
class MissingRecordFrontendController extends frontend\components\controllers\FrontendController
{
    public const SERVICE = frontend\services\UserService::class;
}

$tests = [];
$cases = [
    ['backend', 'read', 'GET', false],
    ['backend', 'update', 'GET', false],
    ['backend', 'update', 'POST', false],
    ['frontend', 'read', 'GET', false],
    ['frontend', 'read', 'GET', true],
    ['inherited', 'read', 'GET', false],
    ['inherited', 'read', 'GET', true],
];
foreach ($cases as [$side, $action, $method, $json]) {
    $name = 'not found: ' . $side . ' ' . $action . ' ' . $method . ($json ? ' JSON' : ' HTML');
    $tests[$name] = static function () use ($side, $action, $method, $json): void {
        $_SERVER['REQUEST_METHOD'] = $method;
        Yii::$app->request->setQueryParams($json ? ['type' => 'json'] : []);
        Yii::$app->request->setBodyParams(['UserForm' => ['name' => 'Must not be saved']]);
        $controller = $side === 'inherited'
            ? new MissingRecordFrontendController('user', Yii::$app, ['layout' => false])
            : generatedController($side);
        $notFound = false;
        try {
            $controller->runAction($action, ['id' => 99999]);
        } catch (yii\web\NotFoundHttpException $error) {
            $notFound = $error->statusCode === 404;
        }
        expect($notFound, 'Missing record did not produce HTTP 404.');
        expect((int) backend\models\items\User::find()->count() === 0, 'The missing-record request wrote to the database.');
    };
}
return $tests;
