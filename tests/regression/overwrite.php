<?php

/**
 * Exercises public generation commands with isolated aliases and filesystem faults.
 *
 * @param string $action Generation entry point.
 * @param string|null $failure Simulated I/O failure, or null for success.
 * @param bool $overwrite Whether replacing an existing file is allowed.
 * @return void
 * @throws Throwable When an invariant is violated.
 */
function checkOverwrite(string $action, ?string $failure, bool $overwrite = true): void
{
    $root = Yii::getAlias('@runtime') . '/' . uniqid('overwrite-');
    $aliases = [];
    foreach (['common', 'backend', 'frontend', 'console'] as $part) {
        $aliases[$part] = Yii::getAlias('@' . $part);
        Yii::setAlias('@' . $part, $root . '/' . $part);
    }
    $paths = [
        'list' => '/common/services/UserService.php',
        'setup' => '/common/components/Entity.php',
        'tests' => '/common/tests/unit/services/UserServiceTest.php',
        'copy' => '/common/copied.php',
    ];
    $target = $root . $paths[$action];
    yii\helpers\FileHelper::createDirectory(dirname($target));
    $original = '<?php /* custom application code */';
    file_put_contents($target, $original);
    $source = $root . '/source.php';
    file_put_contents($source, '<?php /* replacement */');
    $generator = new RegressionGenerator('dnk', Yii::$app);
    $GLOBALS['dnkIoFailure'] = $failure;
    $GLOBALS['dnkIoTriggered'] = false;
    ob_start();
    try {
        try {
            if ($action === 'list') $generator->actionList('user', 'common-service', $overwrite);
            if ($action === 'setup') $generator->actionSetup($overwrite);
            if ($action === 'tests') $generator->actionGenTests('user', $overwrite);
            if ($action === 'copy') {
                $method = new ReflectionMethod(andy87\dnk\GenerateController::class, 'copy');
                $method->setAccessible(true);
                $method->invoke($generator, $source, $target, $overwrite);
            }
        } catch (Throwable $error) {
            if ($failure === null) throw $error;
        }
        if ($failure !== null) expect($GLOBALS['dnkIoTriggered'], 'The I/O fault was not exercised.');
        expect(is_file($target), 'The previous file was deleted.');
        $content = file_get_contents($target);
        if ($failure !== null || !$overwrite) {
            expect($content === $original, 'The previous file was damaged.');
        } else {
            expect($content !== $original && str_starts_with($content, '<?php'), 'Replacement failed.');
        }
        expect(glob(dirname($target) . '/.dnk-*') === [], 'Temporary files were left behind.');
    } finally {
        ob_end_clean();
        unset($GLOBALS['dnkIoFailure'], $GLOBALS['dnkIoTriggered']);
        foreach ($aliases as $part => $path) Yii::setAlias('@' . $part, $path);
    }
}

$tests = [];
foreach (['list', 'setup', 'tests', 'copy'] as $action) {
    foreach (['read', 'write', 'replace', null] as $failure) {
        $tests['overwrite: ' . $action . ' preserves files on ' . ($failure ?? 'successful replacement')] =
            static fn () => checkOverwrite($action, $failure);
    }
}
$tests['overwrite: setup without permission preserves existing files'] = static fn () => checkOverwrite('setup', null, false);
return $tests;
