<?php

declare(strict_types=1);

$root = dirname(__DIR__);
if (!chdir($root)) {
    fwrite(STDERR, "Unable to switch to the repository root.\n");
    exit(1);
}

/**
 * Runs one repository-specific test command.
 *
 * @param string $label   Human-readable test label
 * @param string $command Command line to execute
 */
function runTestCommand(string $label, string $command): void
{
    echo $label . "...\n";
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, $label . ' failed with exit code ' . $exitCode . ".\n");
        exit($exitCode);
    }
}

$commands = [
    ['Verify vendored helper integrity', 'python3 tests/helper_integrity.py'],
    ['Validate repository structure', 'python3 tests/validate_structure.py'],
    ['Run module regression tests', 'php tests/module.php'],
    ['Test library metadata updater', 'python3 tests/test_update_library_metadata.py'],
];

foreach ($commands as [$label, $command]) {
    runTestCommand($label, $command);
}

echo "All OpenShutterButtonControl tests passed.\n";
