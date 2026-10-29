<?php

require __DIR__ . '/../bootstrap.php';

$side = $argv[1] ?? '';
expect(in_array($side, ['common', 'backend', 'frontend'], true), 'Unknown application side.');
$root = prepareApplication();
$status = 0;
try {
    $modelClass = $side . '\\models\\items\\User';
    expect(!class_exists($modelClass, false), 'The fixture model was loaded before its visibility rule.');
    $file = $root . '/' . $side . '/models/items/User.php';
    $scope = <<<'PHP'
/**
     * Restricts this fixture to published records using the normal Yii find hook.
     * @return \yii\db\ActiveQuery Query with the application visibility condition.
     */
    public static function find(): \yii\db\ActiveQuery
    {
        return parent::find()->andWhere(['status_id' => 1]);
    }
PHP;
    file_put_contents($file, str_replace('// custom code ...', $scope, file_get_contents($file)));
    $db = Yii::$app->db;
    $db->createCommand()->insert('user', ['name' => 'Visible scope record', 'status_id' => 1])->execute();
    $visibleId = (int) $db->getLastInsertID();
    $db->createCommand()->insert('user', ['name' => 'Hidden scope record', 'status_id' => 0])->execute();
    $hiddenId = (int) $db->getLastInsertID();
    expect((int) $modelClass::find()->count() === 1, 'The model fixture does not enforce visibility.');
    expect($modelClass::findOne($hiddenId) === null, 'The direct model lookup returned a hidden row.');

    $serviceClass = $side . '\\services\\UserService';
    $service = new $serviceClass();
    expect((int) $service->findWhere([])->count() === 1, 'The service discarded the model visibility condition.');
    expect($service->findWhere(['id' => $hiddenId])->one() === null, 'The service exposed a hidden ID.');
    expect((int) $service->findWhere(['id' => $visibleId])->one()->id === $visibleId, 'The service lost a visible record.');

    $searchClass = $side . '\\models\\search\\UserSearch';
    $provider = (new $searchClass())->search([]);
    expect($provider->getTotalCount() === 1, 'Search bypassed the item visibility condition.');
    expect($provider->getModels()[0] instanceof $modelClass, 'Search did not return the item model of its application side.');
    foreach ([['id' => $hiddenId], ['status_id' => 0]] as $filter) {
        expect((new $searchClass())->search(['UserSearch' => $filter])->getTotalCount() === 0, 'A request filter bypassed visibility.');
    }

    if ($side !== 'common') {
        $controller = generatedController($side);
        $html = $controller->actionList();
        expect(strpos($html, 'Visible scope record') !== false, 'The HTML list lost the visible record.');
        expect(strpos($html, 'Hidden scope record') === false, 'The HTML list exposed the hidden record.');
        expect($controller->actionRead($visibleId) !== '', 'The visible detail page did not render.');
        $actions = $side === 'backend' ? ['read', 'update'] : ['read'];
        foreach ($actions as $action) {
            $_SERVER['REQUEST_METHOD'] = $action === 'update' ? 'POST' : 'GET';
            Yii::$app->request->setBodyParams(['UserForm' => ['name' => 'Unauthorized update']]);
            $notFound = false;
            try {
                $controller->runAction($action, ['id' => $hiddenId]);
            } catch (yii\web\NotFoundHttpException $error) {
                $notFound = $error->statusCode === 404;
            }
            expect($notFound, 'A hidden record did not produce HTTP 404.');
        }
        expect($db->createCommand('SELECT name FROM user WHERE id=:id', [':id' => $hiddenId])->queryScalar() === 'Hidden scope record', 'A hidden record was modified.');
    }

    if ($side === 'frontend') {
        /** Exercises inherited JSON actions with an explicit public field list. */
        $controller = new class('user', Yii::$app, ['layout' => false]) extends frontend\components\controllers\FrontendController {
            public const SERVICE = frontend\services\UserService::class;
            public const JSON_FIELDS = ['id', 'name'];
        };
        Yii::$app->controller = $controller;
        Yii::$app->request->setQueryParams(['type' => 'json']);
        foreach (['actionList' => [], 'actionRead' => [$visibleId]] as $method => $args) {
            $response = $controller->$method(...$args);
            $prepare = new ReflectionMethod(yii\web\Response::class, 'prepare');
            if (PHP_VERSION_ID < 80100) $prepare->setAccessible(true);
            $prepare->invoke($response);
            $data = json_decode($response->content, true, 512, JSON_THROW_ON_ERROR);
            $item = $method === 'actionList' ? $data['items'][0] : $data['item'];
            expect((int) $item['id'] === $visibleId && $item['name'] === 'Visible scope record', 'The JSON record is incorrect.');
            if ($method === 'actionList') expect($data['totalCount'] === 1 && count($data['items']) === 1, 'JSON exposed a hidden row or count.');
            expect(strpos($response->content, 'Hidden scope record') === false, 'JSON exposed hidden data.');
        }
        $notFound = false;
        try {
            $controller->actionRead($hiddenId);
        } catch (yii\web\NotFoundHttpException $error) {
            $notFound = $error->statusCode === 404;
        }
        expect($notFound, 'The hidden JSON detail did not produce HTTP 404.');
    }
    echo 'Visibility passed: ', $side, PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . PHP_EOL);
    $status = 1;
} finally {
    Yii::$app->response->data = null;
    Yii::$app->response->content = null;
    Yii::$app->response->format = yii\web\Response::FORMAT_HTML;
    expect(strpos(realpath($root), realpath(__DIR__ . '/../runtime') . DIRECTORY_SEPARATOR) === 0, 'Refusing to remove a directory outside the test runtime.');
    yii\helpers\FileHelper::removeDirectory($root);
}
exit($status);
