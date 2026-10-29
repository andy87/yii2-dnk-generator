<?php

$tests = [];
foreach (['common', 'backend', 'frontend'] as $side) {
    $tests['visibility: ' . $side . ' service, search and actions preserve model restrictions'] = static function () use ($side): void {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../fixtures/visibility.php')
            . ' ' . escapeshellarg($side) . ' 2>&1';
        exec($command, $output, $status);
        expect($status === 0, implode(PHP_EOL, $output));
        expect(strpos(implode('', $output), 'Visibility passed: ' . $side) !== false, 'The visibility check did not finish.');
    };
}
return $tests;
