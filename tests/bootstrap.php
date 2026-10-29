<?php

use andy87\dnk\GenerateController;
use yii\helpers\FileHelper;

$vendorPath = getenv('DNK_TEST_VENDOR_PATH') ?: dirname(__DIR__) . '/vendor';
if (!is_file($vendorPath . '/autoload.php')) {
    throw new RuntimeException('Install Composer dependencies before running the tests.');
}
require $vendorPath . '/autoload.php';
require $vendorPath . '/yiisoft/yii2/Yii.php';
Yii::setAlias('@andy87/dnk', dirname(__DIR__) . '/src');
Yii::setAlias('@testVendor', $vendorPath);

/**
 * Suppresses console progress while exercising the unmodified generation logic.
 */
class RegressionGenerator extends GenerateController
{
    /**
     * Keeps generation progress out of the test result stream.
     *
     * @param string $string Progress message.
     * @return int Number of suppressed bytes.
     */
    public function stdout($string): int
    {
        return strlen($string);
    }
}

/**
 * Generates an application and opens a fresh in-memory database.
 *
 * @return string Generated application root.
 * @throws Throwable When generation or framework initialization fails.
 */
function prepareApplication(): string
{
    if (!extension_loaded('pdo_sqlite')) {
        throw new RuntimeException('The regression suite requires ext-pdo_sqlite.');
    }

    $root = __DIR__ . '/runtime/' . uniqid('run-', true);
    foreach (['common', 'backend', 'frontend', 'console', 'runtime', 'web/assets'] as $part) {
        FileHelper::createDirectory($root . '/' . $part);
        Yii::setAlias('@' . $part, $root . '/' . $part);
    }
    new yii\console\Application([
        'id' => 'dnk-regression-generator',
        'basePath' => $root,
        'runtimePath' => $root . '/runtime',
        'vendorPath' => Yii::getAlias('@testVendor'),
    ]);
    $generator = new RegressionGenerator('dnk', Yii::$app);
    ob_start();
    try {
        $generator->actionSetup();
        $generator->actionRun('user');
    } finally {
        ob_end_clean();
    }
    copy(__DIR__ . '/fixtures/UserSource.php', $root . '/common/models/sources/UserSource.php');

    Yii::$app = null;
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
    $_SERVER['REQUEST_URI'] = '/index.php?r=user/create';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['SERVER_PORT'] = '80';
    new yii\web\Application([
        'id' => 'dnk-regression-web',
        'basePath' => $root,
        'runtimePath' => $root . '/runtime',
        'vendorPath' => Yii::getAlias('@testVendor'),
        'components' => [
            'db' => ['class' => yii\db\Connection::class, 'dsn' => 'sqlite::memory:'],
            'request' => [
                'cookieValidationKey' => 'regression-only',
                'enableCsrfValidation' => false,
                'scriptUrl' => '/index.php',
                'scriptFile' => __FILE__,
            ],
            'assetManager' => ['basePath' => $root . '/web/assets', 'baseUrl' => '/assets'],
        ],
    ]);
    $vendor = Yii::getAlias('@testVendor');
    Yii::setAlias('@bower', is_dir($vendor . '/bower-asset') ? $vendor . '/bower-asset' : $vendor . '/bower');
    Yii::$app->db->createCommand('CREATE TABLE user (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT, description TEXT, status_id INTEGER, parent_id INTEGER,
        url TEXT, category_image TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        updated_at TEXT DEFAULT CURRENT_TIMESTAMP
    )')->execute();

    return $root;
}

/**
 * Asserts a behavior independently of PHP assertion settings.
 *
 * @param bool $condition Expected invariant.
 * @param string $message Explanation of a failed invariant.
 * @return void
 * @throws RuntimeException When the invariant does not hold.
 */
function expect(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

/**
 * Creates the actual generated controller with its real view templates.
 *
 * @param string $side Application side: backend or frontend.
 * @return yii\web\Controller Generated controller.
 * @throws yii\base\InvalidConfigException When a generated dependency is invalid.
 */
function generatedController(string $side): yii\web\Controller
{
    Yii::$app->setViewPath(Yii::getAlias('@' . $side . '/views'));
    $class = $side . '\\controllers\\UserController';
    $controller = new $class('user', Yii::$app, ['layout' => false]);
    Yii::$app->controller = $controller;

    return $controller;
}

/**
 * Clears only the in-memory fixture and request state between test cases.
 *
 * @return void
 * @throws yii\db\Exception When the fixture cannot be reset.
 */
function resetFixture(): void
{
    Yii::$app->db->createCommand()->delete('user')->execute();
    Yii::$app->request->setBodyParams([]);
    Yii::$app->request->setQueryParams([]);
    Yii::$app->view->params = [];
    $_SERVER['REQUEST_METHOD'] = 'GET';
}
