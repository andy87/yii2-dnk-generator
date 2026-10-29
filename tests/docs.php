<?php

/**
 * Checks published README translations against their source and generator contract.
 * Runs without Composer dependencies or network access.
 */

/**
 * Loads a documentation/source file and rejects unreadable or invalid UTF-8 input.
 *
 * @param string $path Absolute file path.
 * @return string File content with normalized newlines.
 * @throws RuntimeException If the file is missing, unreadable or not UTF-8.
 */
function readDocumentation(string $path): string
{
    if (!is_file($path) || !is_readable($path)) {
        throw new RuntimeException("Cannot read $path");
    }
    $content = file_get_contents($path);
    if ($content === false || !preg_match('//u', $content)) {
        throw new RuntimeException("Invalid UTF-8 or unreadable file: $path");
    }
    return str_replace("\r\n", "\n", $content);
}

/**
 * Extracts a repeated Markdown structure for comparison across translations.
 *
 * @param string $pattern Regular expression with one capture group.
 * @param string $content Document to inspect.
 * @return array Captured values in document order.
 */
function documentationMatches(string $pattern, string $content): array
{
    preg_match_all($pattern, $content, $matches);
    return $matches[1];
}

/**
 * Checks local destinations and explicit fragment anchors, ignoring code samples.
 *
 * @param string $path Absolute path of the source Markdown file.
 * @param string $content Source Markdown.
 * @return int Number of verified local links.
 * @throws RuntimeException If a local target or explicit anchor is missing.
 */
function checkDocumentationLinks(string $path, string $content): int
{
    $prose = preg_replace('/^```[^\n]*\n.*?^```\h*$/ms', '', $content);
    $links = documentationMatches('/\]\(([^)]+)\)/', $prose);
    $count = 0;
    foreach ($links as $link) {
        if (preg_match('/^(?:[a-z][a-z0-9+.-]*:|\/\/)/i', $link)) continue;
        [$target, $anchor] = array_pad(explode('#', $link, 2), 2, null);
        $resolved = $target === '' ? $path : realpath(dirname($path) . '/' . rawurldecode($target));
        if ($resolved === false || !file_exists($resolved)) {
            throw new RuntimeException("Broken local link in $path: $link");
        }
        if ($anchor !== null) {
            $targetContent = readDocumentation($resolved);
            $anchors = documentationMatches('/<a id="([^"]+)"><\/a>/', $targetContent);
            if (!in_array(rawurldecode($anchor), $anchors, true)) {
                throw new RuntimeException("Missing explicit anchor in $path: $link");
            }
        }
        $count++;
    }
    return $count;
}

try {
    $root = dirname(__DIR__);
    $source = readDocumentation($root . '/README.md');
    $generator = readDocumentation($root . '/src/GenerateController.php');
    $languages = ['ru', 'kk', 'zh-CN', 'es', 'pt-BR', 'de', 'fr', 'ja', 'id'];
    $files = [$root . '/README.md'];
    foreach ($languages as $language) $files[] = $root . '/docs/i18n/README.' . $language . '.md';

    $structures = [
        'sections' => '/<a id="([^"]+)"><\/a>/',
        'code examples' => '/^(```[^\n]*\n.*?^```\h*)$/ms',
        'CLI signatures' => '/^\| `(dnk\/[^`]+)` \|/m',
        'mapping rows' => '/^(\| `[^`]+` \| `@[^`]+` \|)$/m',
        'base-class defaults' => '/^(\| `BASE_[^`]+` \| `[^`]+` \|)$/m',
    ];
    $localLinks = 0;
    foreach ($files as $file) {
        $content = readDocumentation($file);
        if (preg_match('/@@[a-z_]+@@/', $content)) throw new RuntimeException("Unresolved document marker: $file");
        if (substr_count($content, '```') % 2 !== 0) throw new RuntimeException("Unclosed code fence: $file");
        foreach ($structures as $name => $pattern) {
            $expected = documentationMatches($pattern, $source);
            if ($expected === [] || documentationMatches($pattern, $content) !== $expected) {
                throw new RuntimeException("Translation differs in $name: $file");
            }
        }
        $localLinks += checkDocumentationLinks($file, $content);
    }
    $index = $root . '/docs/i18n/README.md';
    $localLinks += checkDocumentationLinks($index, readDocumentation($index));

    $actions = documentationMatches('/public function action([A-Z][a-zA-Z]+)\(/', $generator);
    $commands = array_map(static fn(string $action): string => strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $action)), $actions);
    $documented = array_unique(documentationMatches('/\bdnk\/([a-z][a-z-]*)/', $source));
    sort($commands);
    sort($documented);
    if ($commands !== $documented) throw new RuntimeException('README command list differs from GenerateController actions.');

    preg_match('/private const MAPPING = \[(.*?)\n    \];/s', $generator, $mapping);
    preg_match_all('/\x27([^\x27]+)\x27 => \[\s*self::SOURCE => \x27[^\x27]+\x27\s*,\s*self::TARGET => ([\x27"])(.*?)\2/s', $mapping[1], $entries, PREG_SET_ORDER);
    $rows = documentationMatches($structures['mapping rows'], $source);
    $expectedRows = [];
    foreach ($entries as $entry) $expectedRows[] = '| `' . $entry[1] . '` | `' . $entry[3] . '` |';
    if ($expectedRows === [] || $rows !== $expectedRows) throw new RuntimeException('README generated-file mapping differs from the generator.');

    foreach (['SETUP_GENERATE_SETUP_FILES', 'GENERATE_TESTS'] as $constant) {
        preg_match('/const ' . $constant . ' = \[(.*?)\n    \];/s', $generator, $mapping);
        $targets = documentationMatches('/self::TARGET => [\x27"]([^\x27"]+)[\x27"]/', $mapping[1]);
        if ($targets === [] || !str_contains($source, "```text\n" . implode("\n", $targets) . "\n```")) {
            throw new RuntimeException("README file list differs from $constant.");
        }
    }
    echo 'Documentation OK: ' . count($files) . ' editions, ' . count($commands) . ' commands, '
        . count($expectedRows) . ' mappings, ' . $localLinks . " local links.\n";
} catch (Throwable $error) {
    fwrite(STDERR, $error->getMessage() . "\n");
    exit(1);
}
