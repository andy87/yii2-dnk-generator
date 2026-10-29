<?php

// A controlled child process tests process handling, not Gii's code generation.
file_put_contents('calls.jsonl', json_encode(['arguments' => array_slice($argv, 1), 'php' => PHP_BINARY]) . "\n", FILE_APPEND);
if (in_array('--tableName=bad', $argv, true) || $argv[1] === 'gii/crud') {
    fwrite(STDERR, "Controlled child failure\n");
    exit(23);
}
echo "Controlled child success\n";
