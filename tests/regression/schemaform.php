<?php

$tests = [];
foreach (['minimal', 'columns'] as $kind) {
    $tests['schema form: ' . $kind . ' schema supports rendering, validation and persistence'] = static function () use ($kind): void {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../fixtures/schema-form.php')
            . ' ' . escapeshellarg($kind) . ' 2>&1';
        exec($command, $output, $status);
        expect($status === 0, implode(PHP_EOL, $output));
        expect(strpos(implode('', $output), 'Schema form passed: ' . $kind) !== false, 'The schema form check did not finish.');
    };
}
return $tests;
