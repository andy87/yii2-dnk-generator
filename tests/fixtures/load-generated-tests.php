<?php

namespace Codeception\Test {
    /**
     * Supplies only the parent declaration for the isolated class-loading check.
     * This fixture does not execute or emulate Codeception tests.
     */
    abstract class Unit {}
}

namespace {
    $root = $argv[1];
    $loaded = 0;
    foreach (['common', 'backend', 'frontend'] as $side) {
        foreach (['services', 'models/items', 'models/forms'] as $path) {
            foreach (glob($root . '/' . $side . '/tests/unit/' . $path . '/*Test.php') as $file) {
                require $file;
                $loaded++;
            }
        }
    }
    if ($loaded !== 8) throw new \RuntimeException('Expected eight generated test classes.');
    foreach (['backend', 'frontend'] as $side) {
        if (!class_exists($side . '\\tests\\unit\\models\\forms\\UserTest', false)) {
            throw new \RuntimeException('The form test namespace does not match its directory.');
        }
    }
    echo 'Loaded all eight generated test classes.';
}
