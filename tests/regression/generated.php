<?php

return [
    'generated tests: all model, form and service classes load together' => function (): void {
        ob_start();
        try {
            (new RegressionGenerator('dnk', Yii::$app))->actionGenTests('user');
        } finally {
            ob_end_clean();
        }
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../fixtures/load-generated-tests.php')
            . ' ' . escapeshellarg(Yii::$app->basePath) . ' 2>&1';
        exec($command, $output, $status);
        expect($status === 0, implode(PHP_EOL, $output));
        expect(implode('', $output) === 'Loaded all eight generated test classes.', 'The class-loading check did not finish.');
    },
];
