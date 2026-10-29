<?php

require __DIR__ . '/bootstrap.php';

$group = $argv[1] ?? '*';
if (!preg_match('/^(?:[a-z]+|\*)$/', $group)) {
    throw new InvalidArgumentException('Expected a test group name.');
}
$files = glob(__DIR__ . '/regression/' . $group . '.php');
if (!$files) throw new RuntimeException('No matching regression tests.');

$root = prepareApplication();
$passed = 0;
$failed = 0;
foreach ($files as $file) {
    foreach (require $file as $name => $test) {
        try {
            resetFixture();
            $test();
            echo 'PASS ', $name, PHP_EOL;
            $passed++;
        } catch (Throwable $error) {
            echo 'FAIL ', $name, ': ', get_class($error), ': ', $error->getMessage(), PHP_EOL;
            $failed++;
        }
    }
}
echo $passed, ' passed; ', $failed, ' failed; PHP ', PHP_VERSION, '; Yii ', Yii::getVersion(), PHP_EOL;
if ($failed) {
    echo 'Generated application retained at ', $root, PHP_EOL;
} else {
    // $root is freshly created by prepareApplication(), never a caller-supplied path.
    yii\helpers\FileHelper::removeDirectory($root);
}
exit($failed ? 1 : 0);
