<?php

/** Models an application that explicitly grants CRUD to one backend permission. */
class PermissionController extends backend\controllers\UserController
{
    /** @var string[] Permissions intentionally enabled by the application. */
    public array $accessRoles = ['regression.backend'];
}

/**
 * Executes real Yii routes with CSRF validation and isolated authentication.
 * @param int|null $userId Guest, unprivileged user 2, or permitted user 1.
 * @param bool $configured Whether the application configured its backend permission.
 * @param callable $check Assertions using the issued CSRF token.
 * @return void
 * @throws Throwable When routing or assertions fail.
 */
function withBackendAccess(?int $userId, bool $configured, callable $check): void
{
    $mapping = Yii::$app->controllerMap;
    Yii::$app->controllerMap['user'] = [
        'class' => $configured ? PermissionController::class : backend\controllers\UserController::class,
        'layout' => false,
    ];
    Yii::$app->setViewPath(Yii::getAlias('@backend/views'));
    Yii::$app->user->setIdentity($userId === null ? null : new RegressionIdentity($userId));
    Yii::$app->request->enableCsrfValidation = true;
    try {
        $check(Yii::$app->request->getCsrfToken());
    } finally {
        Yii::$app->controllerMap = $mapping;
        Yii::$app->request->enableCsrfValidation = false;
        Yii::$app->user->setIdentity(new RegressionIdentity());
    }
}

$tests = [];
foreach (['guest' => [null, true], 'user without permission' => [2, true], 'unconfigured backend' => [1, false]] as $name => [$userId, $configured]) {
    $tests['access: ' . $name . ' cannot read or write backend records'] = static function () use ($userId, $configured): void {
        $model = (new backend\services\UserService())->create(['name' => 'Protected record']);
        withBackendAccess($userId, $configured, static function (string $token) use ($model): void {
            foreach (['GET' => ['list', 'read', 'create', 'update'], 'POST' => ['create', 'update']] as $method => $actions) {
                foreach ($actions as $action) {
                    $_SERVER['REQUEST_METHOD'] = $method;
                    Yii::$app->request->setBodyParams([
                        Yii::$app->request->csrfParam => $token, 'UserForm' => ['name' => 'Forbidden change'],
                    ]);
                    $denied = false;
                    try {
                        Yii::$app->runAction('user/' . $action, ['id' => $model->id]);
                    } catch (yii\web\ForbiddenHttpException $error) {
                        $denied = $error->statusCode === 403;
                    }
                    expect($denied, "$method $action was accessible without permission.");
                }
            }
        });
        expect((int) backend\models\items\User::find()->count() === 1, 'Denied requests created a record.');
        expect(backend\models\items\User::findOne($model->id)->name === 'Protected record', 'Denied requests changed a record.');
    };
}
$tests['access: an authorized user retains CRUD and CSRF protection'] = static function (): void {
    withBackendAccess(1, true, static function (string $token): void {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        expect(strpos(Yii::$app->runAction('user/create'), $token) !== false, 'The authorized form did not render its CSRF token.');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        Yii::$app->request->setBodyParams(['UserForm' => ['name' => 'Missing CSRF']]);
        $rejected = false;
        try {
            Yii::$app->runAction('user/create');
        } catch (yii\web\BadRequestHttpException $error) {
            $rejected = $error->statusCode === 400;
        }
        expect($rejected && backend\models\items\User::find()->count() == 0, 'CSRF rejection was bypassed.');
        Yii::$app->request->setBodyParams([Yii::$app->request->csrfParam => $token, 'UserForm' => ['name' => 'Permitted create']]);
        Yii::$app->runAction('user/create');
        $model = backend\models\items\User::findOne(['name' => 'Permitted create']);
        expect($model !== null, 'The permitted create failed.');
        Yii::$app->request->setBodyParams([Yii::$app->request->csrfParam => $token, 'UserForm' => ['name' => 'Permitted update']]);
        Yii::$app->runAction('user/update', ['id' => $model->id]);
        expect(backend\models\items\User::findOne($model->id)->name === 'Permitted update', 'The permitted update failed.');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        expect(strpos(Yii::$app->runAction('user/list'), 'Permitted update') !== false, 'The permitted list failed.');
        expect(Yii::$app->runAction('user/read', ['id' => $model->id]) !== '', 'The permitted read failed.');
    });
};
$tests['access: frontend pages remain public'] = static function (): void {
    $model = (new frontend\services\UserService())->create(['name' => 'Public record']);
    Yii::$app->user->setIdentity(null);
    $controller = generatedController('frontend');
    expect(strpos($controller->runAction('list'), 'Public record') !== false, 'Public list was restricted.');
    expect($controller->runAction('read', ['id' => $model->id]) !== '', 'Public read was restricted.');
};
return $tests;
