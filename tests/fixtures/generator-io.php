<?php

namespace andy87\dnk;

/**
 * Simulates unreadable templates without modifying the installed package.
 *
 * @param string $filename Template path.
 * @return string|false File contents or a simulated read failure.
 */
function file_get_contents(string $filename): string|false
{
    if (($GLOBALS['dnkIoFailure'] ?? null) === 'read') {
        $GLOBALS['dnkIoTriggered'] = true;
        return false;
    }
    return \file_get_contents($filename);
}

/**
 * Simulates a partial disk write to exercise preservation of the old file.
 *
 * @param string $filename Destination path.
 * @param string $content Requested file contents.
 * @return int|false Number of bytes actually written.
 */
function file_put_contents(string $filename, string $content): int|false
{
    if (($GLOBALS['dnkIoFailure'] ?? null) === 'write') {
        $GLOBALS['dnkIoTriggered'] = true;
        return \file_put_contents($filename, substr($content, 0, 1));
    }
    return \file_put_contents($filename, $content);
}

/**
 * Simulates a failed replacement, such as a destination locked on Windows.
 *
 * @param string $from Prepared file.
 * @param string $to Destination file.
 * @return bool Whether replacement succeeded.
 */
function rename(string $from, string $to): bool
{
    if (($GLOBALS['dnkIoFailure'] ?? null) === 'replace') {
        $GLOBALS['dnkIoTriggered'] = true;
        return false;
    }
    return \rename($from, $to);
}
