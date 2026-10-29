<?php

require __DIR__ . '/bootstrap.php';

$runner = getenv('DNK_TEST_CODECEPTION') ?: Yii::getAlias('@testVendor/bin/codecept');
if (!is_file($runner)) {
    throw new RuntimeException('Install Codeception in the test vendor directory or set DNK_TEST_CODECEPTION to codecept.phar.');
}

/**
 * Runs the real Codeception lifecycle and retains its diagnostics on failure.
 *
 * @param string $runner Installed Codeception entry point or PHAR.
 * @param string $root Isolated suite directory.
 * @param string $command Fixed build or run unit command.
 * @return void
 * @throws RuntimeException When Codeception reports a failure.
 */
function runCodeception(string $runner, string $root, string $command): void
{
    exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($runner) . ' ' . $command
        . ' -c ' . escapeshellarg($root) . ' 2>&1', $output, $status);
    if ($status !== 0) throw new RuntimeException(implode(PHP_EOL, $output));
}

$root = prepareApplication();
$status = 0;
$cases = 0;
try {
    ob_start();
    try {
        (new RegressionGenerator('dnk', Yii::$app))->actionGenTests('user');
    } finally {
        ob_end_clean();
    }
    foreach (['legacy' => '', 'support' => 'Support'] as $layout => $support) {
        foreach (['common', 'backend', 'frontend'] as $side) {
            $suite = $root . '/codeception/' . $layout . '/' . $side;
            foreach (['unit', '_output', '_data', '_support'] as $directory) {
                yii\helpers\FileHelper::createDirectory($suite . '/tests/' . $directory);
            }
            $supportConfig = $support === '' ? "''" : $support;
            file_put_contents($suite . '/codeception.yml', "namespace: $side\\tests\nsupport_namespace: $supportConfig\npaths:\n    tests: tests\n    output: tests/_output\n    data: tests/_data\n    support: tests/_support\nactor_suffix: Tester\nsettings:\n    colors: false\n");
            file_put_contents($suite . '/tests/unit.suite.yml', "actor: UnitTester\nmodules:\n    enabled: []\n");
            $actor = $side . '\\tests\\' . ($support === '' ? '' : $support . '\\') . 'UnitTester';
            foreach (['services' => 'Service', 'models/items' => 'Item', 'models/forms' => 'Form'] as $path => $name) {
                foreach (glob($root . '/' . $side . '/tests/unit/' . $path . '/*Test.php') as $file) {
                    $parent = $side . '\\tests\\unit\\' . str_replace('/', '\\', $path) . '\\' . pathinfo($file, PATHINFO_FILENAME);
                    // Keep the generated property and real _setUp(), replacing only the example test body.
                    $probe = "<?php\nnamespace $side\\tests\\probe;\nrequire_once " . var_export($file, true) . ";\n"
                        . "/** Checks real actor injection into the unmodified generated declaration. */\n"
                        . "class {$name}ProbeTest extends \\$parent\n{\n"
                        . "    /** @return void Verifies the actor supplied by the current suite. */\n"
                        . "    public function testOne(): void\n    {\n"
                        . '        self::assertInstanceOf(\\Codeception\\Actor::class, $this->tester);' . "\n"
                        . '        self::assertSame(' . var_export($actor, true) . ', get_class($this->tester));' . "\n    }\n}\n";
                    file_put_contents($suite . '/tests/unit/' . $name . 'ProbeTest.php', $probe);
                    $cases++;
                }
            }
            runCodeception($runner, $suite, 'build');
            runCodeception($runner, $suite, 'run unit');
            echo 'PASS Codeception: ', $side, ' / ', $layout, PHP_EOL;
        }
    }
    expect($cases === 16, 'Expected all eight generated test classes in both actor layouts.');
    echo $cases, ' generated Codeception tests passed; 32 actor assertions.', PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . PHP_EOL);
    $status = 1;
} finally {
    expect(strpos(realpath($root), realpath(__DIR__ . '/runtime') . DIRECTORY_SEPARATOR) === 0, 'Refusing to remove a directory outside the test runtime.');
    yii\helpers\FileHelper::removeDirectory($root);
}
exit($status);
