<?php

$tests = [];
foreach (['table-only', 'labels'] as $mode) {
    $tests['registration: new entity pages and migration with ' . $mode] = static function () use ($mode): void {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../fixtures/registered-entity.php')
            . ' ' . escapeshellarg($mode) . ' 2>&1';
        exec($command, $output, $status);
        expect($status === 0, implode(PHP_EOL, $output));
        expect(strpos(implode('', $output), 'Registered entity pages and migration passed.') !== false, 'The entity check did not finish.');
    };
}
$tests['registration: unknown entity reports a configuration error'] = static function (): void {
    foreach (['getLabelMany', 'getLabelOne'] as $method) {
        $rejected = false;
        try {
            common\components\Entity::$method('unregistered');
        } catch (yii\base\InvalidConfigException $error) {
            $rejected = strpos($error->getMessage(), 'unregistered') !== false;
        }
        expect($rejected, 'An unknown entity did not produce a useful configuration error.');
    }
};
return $tests;
