<?php

// Runs the real DNK action through Yii's console exit-code handling.
$vendor = getenv('DNK_TEST_VENDOR_PATH') ?: dirname(__DIR__, 2) . '/vendor';
require $vendor . '/autoload.php';
require $vendor . '/yiisoft/yii2/Yii.php';
Yii::setAlias('@andy87/dnk', dirname(__DIR__, 2) . '/src');
$app = new yii\console\Application([
    'id' => 'gii-exit-regression',
    'basePath' => getcwd(),
    'components' => ['request' => ['params' => ['dnk/' . $argv[1], $argv[2]]]],
    'controllerMap' => ['dnk' => [
        'class' => andy87\dnk\GenerateController::class,
        'command' => [
            andy87\dnk\GenerateController::COMMAND_MODEL => '--tableName={{snake_case}}',
            andy87\dnk\GenerateController::COMMAND_CRUD => '--modelClass={{CamelCase}} --viewPath=@backend/views/{{kebab-case}}-crud',
        ],
    ]],
]);
exit($app->run());
