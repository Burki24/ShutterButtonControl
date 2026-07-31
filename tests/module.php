<?php

declare(strict_types=1);

$constants = [
    'VARIABLE_PRESENTATION_VALUE_PRESENTATION' => '{3319437D-7CDE-699D-750A-3C6A3841FA75}',
    'VARIABLETYPE_BOOLEAN'                     => 0,
    'VARIABLETYPE_INTEGER'                     => 1,
    'VARIABLETYPE_FLOAT'                       => 2,
    'VARIABLETYPE_STRING'                      => 3,
    'VM_UPDATE'                                => 10603,
    'IS_ACTIVE'                                => 102,
    'IS_INACTIVE'                              => 104
];
foreach ($constants as $constant => $value) {
    if (!defined($constant)) {
        define($constant, $value);
    }
}

/** @var array<int, array{type: int, value: mixed}> $GLOBALS['SBC_VARIABLES'] */
$GLOBALS['SBC_VARIABLES'] = [];
/** @var array<int, array<string, int>> $GLOBALS['SBC_IDENT_MAP'] */
$GLOBALS['SBC_IDENT_MAP'] = [];
/** @var list<array{id: int, value: mixed}> $GLOBALS['SBC_REQUEST_ACTIONS'] */
$GLOBALS['SBC_REQUEST_ACTIONS'] = [];

function IPS_VariableExists(int $variableID): bool
{
    return isset($GLOBALS['SBC_VARIABLES'][$variableID]);
}

/** @return array{VariableType: int} */
function IPS_GetVariable(int $variableID): array
{
    return ['VariableType' => $GLOBALS['SBC_VARIABLES'][$variableID]['type']];
}

function GetValue(int $variableID): mixed
{
    return $GLOBALS['SBC_VARIABLES'][$variableID]['value'];
}

function SetValue(int $variableID, mixed $value): void
{
    $GLOBALS['SBC_VARIABLES'][$variableID]['value'] = $value;
}

function RequestAction(int $variableID, mixed $value): void
{
    $GLOBALS['SBC_REQUEST_ACTIONS'][] = ['id' => $variableID, 'value' => $value];
}

function IPS_GetObjectIDByIdent(string $ident, int $parentID): int|false
{
    return $GLOBALS['SBC_IDENT_MAP'][$parentID][$ident] ?? false;
}

function IPS_SetIdent(int $objectID, string $ident): void
{
    foreach ($GLOBALS['SBC_IDENT_MAP'] as $parentID => $idents) {
        foreach ($idents as $oldIdent => $mappedID) {
            if ($mappedID !== $objectID) {
                continue;
            }

            unset($GLOBALS['SBC_IDENT_MAP'][$parentID][$oldIdent]);
            $GLOBALS['SBC_IDENT_MAP'][$parentID][$ident] = $objectID;
            return;
        }
    }
}

class IPSModuleStrict
{
    public int $InstanceID = 4242;

    /** @var array<string, int> */
    public array $properties = [];
    /** @var array<string, int|float|bool> */
    public array $attributes = [];
    /** @var array<string, int> */
    public array $timers = [];
    /** @var array<int, array<int, bool>> */
    public array $messages = [];
    /** @var array<string, array<string, mixed>> */
    public array $maintainedVariables = [];
    /** @var list<array{message: string, data: string}> */
    public array $debug = [];
    public int $status = 0;
    public bool $destroyed = false;

    public function Create(): void
    {
    }

    public function ApplyChanges(): void
    {
    }

    public function Destroy(): void
    {
        $this->destroyed = true;
    }

    public function RegisterPropertyInteger(string $name, int $default): void
    {
        $this->properties[$name] ??= $default;
    }

    public function ReadPropertyInteger(string $name): int
    {
        return $this->properties[$name];
    }

    public function RegisterAttributeInteger(string $name, int $default): void
    {
        $this->attributes[$name] ??= $default;
    }

    public function RegisterAttributeFloat(string $name, float $default): void
    {
        $this->attributes[$name] ??= $default;
    }

    public function RegisterAttributeBoolean(string $name, bool $default): void
    {
        $this->attributes[$name] ??= $default;
    }

    public function ReadAttributeInteger(string $name): int
    {
        return (int) $this->attributes[$name];
    }

    public function ReadAttributeFloat(string $name): float
    {
        return (float) $this->attributes[$name];
    }

    public function ReadAttributeBoolean(string $name): bool
    {
        return (bool) $this->attributes[$name];
    }

    public function WriteAttributeInteger(string $name, int $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function WriteAttributeFloat(string $name, float $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function WriteAttributeBoolean(string $name, bool $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function RegisterTimer(string $ident, int $interval, string $script): void
    {
        $this->timers[$ident] = $interval;
    }

    public function SetTimerInterval(string $ident, int $interval): void
    {
        $this->timers[$ident] = $interval;
    }

    public function RegisterMessage(int $senderID, int $message): void
    {
        $this->messages[$senderID][$message] = true;
    }

    public function UnregisterMessage(int $senderID, int $message): void
    {
        unset($this->messages[$senderID][$message]);
    }

    /** @param array<string, mixed>|string $presentation */
    public function MaintainVariable(
        string $ident,
        string $name,
        int $type,
        array|string $presentation,
        int $position,
        bool $keep
    ): void {
        $objectID = $GLOBALS['SBC_IDENT_MAP'][$this->InstanceID][$ident] ?? 10000 + count($this->maintainedVariables);
        $GLOBALS['SBC_IDENT_MAP'][$this->InstanceID][$ident] = $objectID;
        $GLOBALS['SBC_VARIABLES'][$objectID] ??= ['type' => $type, 'value' => $type === VARIABLETYPE_STRING ? '' : 0];
        $this->maintainedVariables[$ident] = [
            'name'         => $name,
            'type'         => $type,
            'presentation' => $presentation,
            'position'     => $position,
            'keep'         => $keep,
            'id'           => $objectID
        ];
    }

    public function SetValue(string $ident, mixed $value): void
    {
        $objectID = $GLOBALS['SBC_IDENT_MAP'][$this->InstanceID][$ident];
        SetValue($objectID, $value);
    }

    public function SetStatus(int $status): void
    {
        $this->status = $status;
    }

    public function Translate(string $text): string
    {
        return $text;
    }

    public function SendDebug(string $message, mixed $data, int $format): void
    {
        $this->debug[] = ['message' => $message, 'data' => (string) $data];
    }
}

require_once __DIR__ . '/../ShutterButton/module.php';

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(
            $message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true)
        );
    }
}

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function invokePrivate(object $object, string $method, mixed ...$arguments): mixed
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);

    return $reflection->invoke($object, ...$arguments);
}

$GLOBALS['SBC_VARIABLES'] = [
    10  => ['type' => VARIABLETYPE_BOOLEAN, 'value' => false],
    11  => ['type' => VARIABLETYPE_STRING, 'value' => 'released'],
    20  => ['type' => VARIABLETYPE_STRING, 'value' => 'STOP'],
    30  => ['type' => VARIABLETYPE_INTEGER, 'value' => 50],
    40  => ['type' => VARIABLETYPE_BOOLEAN, 'value' => false],
    501 => ['type' => VARIABLETYPE_INTEGER, 'value' => 250],
    502 => ['type' => VARIABLETYPE_STRING, 'value' => 'ShortPress']
];
$GLOBALS['SBC_IDENT_MAP'][4242] = [
    'LastDuration' => 501,
    'LastAction'   => 502
];

$module = new ShutterButton();
$module->Create();
assertSameValue(1000, $module->properties['ShortPressTime'], 'The default long-press threshold changed unexpectedly.');
assertTrue(!isset($module->properties['PositionUp']), 'Unused PositionUp property must not be registered.');
assertTrue(!isset($module->properties['PositionDown']), 'Unused PositionDown property must not be registered.');

$module->properties['ButtonID'] = 10;
$module->properties['MoveID'] = 20;
$module->properties['PositionID'] = 30;
$module->ApplyChanges();

assertSameValue(IS_ACTIVE, $module->status, 'A valid configuration must activate the module.');
assertTrue(isset($module->messages[10][VM_UPDATE]), 'The button update message was not registered.');
assertSameValue(10, $module->attributes['RegisteredButtonID'], 'The registered button ID attribute is incorrect.');
assertSameValue(501, $GLOBALS['SBC_IDENT_MAP'][4242]['last_duration_ms'], 'Legacy duration variable was not renamed in place.');
assertSameValue(502, $GLOBALS['SBC_IDENT_MAP'][4242]['last_action'], 'Legacy action variable was not renamed in place.');
assertSameValue('short_press', GetValue(502), 'Legacy action value was not normalized.');

$durationPresentation = $module->maintainedVariables['last_duration_ms']['presentation'];
assertSameValue(' ms', $durationPresentation['SUFFIX'] ?? null, 'Duration presentation must use milliseconds.');
$actionPresentation = $module->maintainedVariables['last_action']['presentation'];
assertSameValue(
    VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    $actionPresentation['PRESENTATION'] ?? null,
    'Last action must use a native value presentation.'
);
$options = json_decode((string) ($actionPresentation['OPTIONS'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
assertSameValue('short_press', $options[1]['Value'] ?? null, 'Short-press presentation option is missing.');
assertSameValue('long_press', $options[2]['Value'] ?? null, 'Long-press presentation option is missing.');

// Short press while opening: position mode 0 means 0=open and 100=closed.
$GLOBALS['SBC_REQUEST_ACTIONS'] = [];
$GLOBALS['SBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->attributes['PressStart'] = microtime(true) - 0.2;
$GLOBALS['SBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue([['id' => 30, 'value' => 0]], $GLOBALS['SBC_REQUEST_ACTIONS'], 'Short opening press must target position 0.');
assertSameValue('short_press', GetValue(502), 'Short press status was not stored.');
assertTrue(GetValue(501) >= 150 && GetValue(501) <= 400, 'Short-press duration was not measured plausibly.');

// Duplicate release messages must not trigger another action.
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue(1, count($GLOBALS['SBC_REQUEST_ACTIONS']), 'Duplicate release update triggered an extra action.');

// Long press while opening: OPEN on threshold, STOP on release.
$GLOBALS['SBC_REQUEST_ACTIONS'] = [];
$GLOBALS['SBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->RequestAction('HandleLongPress', 0);
$GLOBALS['SBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue(
    [
        ['id' => 20, 'value' => 'OPEN'],
        ['id' => 20, 'value' => 'STOP']
    ],
    $GLOBALS['SBC_REQUEST_ACTIONS'],
    'Long opening press must send OPEN followed by STOP.'
);
assertSameValue('long_press', GetValue(502), 'Long press status was not stored.');

// Closing with 0=open / 100=closed must target 100 after a short press.
$GLOBALS['SBC_REQUEST_ACTIONS'] = [];
$module->properties['Direction'] = 1;
$GLOBALS['SBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->attributes['PressStart'] = microtime(true) - 0.1;
$GLOBALS['SBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue([['id' => 30, 'value' => 100]], $GLOBALS['SBC_REQUEST_ACTIONS'], 'Short closing press must target position 100.');

assertSameValue(true, invokePrivate($module, 'MapButtonState', 'pressed'), 'Text state pressed must map to true.');
assertSameValue(false, invokePrivate($module, 'MapButtonState', 'OFF'), 'Text state OFF must map to false.');
assertSameValue(null, invokePrivate($module, 'MapButtonState', 'toggle'), 'Unsupported text state must remain unknown.');
assertSameValue(true, invokePrivate($module, 'MapButtonState', 1), 'Numeric state 1 must map to true.');
assertSameValue(false, invokePrivate($module, 'MapButtonState', 0.0), 'Numeric state 0 must map to false.');

// Changing the selected button must unregister the old sender first.
$module->properties['ButtonID'] = 11;
$module->ApplyChanges();
assertTrue(!isset($module->messages[10][VM_UPDATE]), 'Old button registration was not removed.');
assertTrue(isset($module->messages[11][VM_UPDATE]), 'New button registration was not created.');

// Invalid variable types must produce explicit configuration states.
$module->properties['ButtonID'] = 99;
$module->ApplyChanges();
assertSameValue(201, $module->status, 'Missing button variable must set status 201.');
$module->properties['ButtonID'] = 10;
$module->properties['MoveID'] = 30;
$module->ApplyChanges();
assertSameValue(202, $module->status, 'Non-string movement variable must set status 202.');
$module->properties['MoveID'] = 20;
$module->properties['PositionID'] = 40;
$module->ApplyChanges();
assertSameValue(203, $module->status, 'Non-numeric position variable must set status 203.');

$invalidIdentThrown = false;
try {
    $module->RequestAction('Unknown', 0);
} catch (InvalidArgumentException) {
    $invalidIdentThrown = true;
}
assertTrue($invalidIdentThrown, 'Unknown RequestAction identifiers must throw an exception.');

$module->properties['PositionID'] = 30;
$module->ApplyChanges();
$module->Destroy();
assertTrue($module->destroyed, 'Parent Destroy() was not called.');
assertTrue(!isset($module->messages[10][VM_UPDATE]), 'Button registration must be removed during Destroy().');
assertSameValue(0, $module->timers['LongPress'], 'Long-press timer must be stopped during Destroy().');

echo "ShutterButton module regression tests passed.\n";
