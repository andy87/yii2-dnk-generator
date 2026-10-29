<?php

use andy87\dnk\GenerateController;

/**
 * Runs setup under fresh aliases without changing the main test application.
 *
 * @param string $name Isolated setup directory name.
 * @param array $config Generator overrides.
 * @return string Directory containing generated setup files.
 * @throws Throwable On invalid configuration or file generation errors.
 */
function generateSetup(string $name, array $config = []): string
{
    $root = Yii::getAlias('@runtime') . '/' . $name;
    $aliases = [];
    foreach (['common', 'backend', 'frontend', 'console'] as $part) {
        $aliases[$part] = Yii::getAlias('@' . $part);
        Yii::setAlias('@' . $part, $root . '/' . $part);
    }
    ob_start();
    try {
        (new RegressionGenerator('dnk', Yii::$app, $config))->actionSetup();
    } finally {
        ob_end_clean();
        foreach ($aliases as $part => $path) Yii::setAlias('@' . $part, $path);
    }
    return $root;
}

return [
    'setup: default configuration generates ten valid PHP files' => function (): void {
        $root = generateSetup('default');
        $files = yii\helpers\FileHelper::findFiles($root, ['only' => ['*.php']]);
        expect(count($files) === 10, 'Expected ten setup files.');
        foreach ($files as $file) {
            $output = [];
            exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $status);
            expect($status === 0, implode(PHP_EOL, $output));
        }
    },
    'setup: partial overrides retain the other defaults' => function (): void {
        $root = generateSetup('partial', ['baseClasses' => [
            GenerateController::BASE_RESOURCE_CLASS => 'app\\CustomResource',
        ]]);
        $resource = file_get_contents($root . '/common/components/resources/crud/CreateResource.php');
        $controller = file_get_contents($root . '/backend/components/controllers/BackendController.php');
        expect(strpos($resource, 'use app\\CustomResource as BaseResourceClass;') !== false, 'Override was ignored.');
        expect(strpos($controller, 'use andy87\\dnk\\source\\base\\BaseController as BaseControllerClass;') !== false, 'Default was lost.');
    },
    'setup: invalid base classes fail before creating files' => function (): void {
        foreach ([null, '', 'not a class', [], 'class', '\\'] as $index => $value) {
            $rejected = false;
            try {
                generateSetup('invalid-' . $index, ['baseClasses' => [GenerateController::BASE_RESOURCE_CLASS => $value]]);
            } catch (yii\base\InvalidConfigException $error) {
                $rejected = true;
            }
            expect($rejected, 'Invalid base class was not rejected.');
            expect(!is_dir(Yii::getAlias('@runtime/invalid-' . $index)), 'Invalid configuration wrote files.');
        }
    },
];
