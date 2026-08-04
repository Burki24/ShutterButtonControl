<?php

declare(strict_types=1);

$constants = [
    'VARIABLE_PRESENTATION_VALUE_PRESENTATION' => '{3319437D-7CDE-699D-750A-3C6A3841FA75}',
    'VARIABLETYPE_BOOLEAN'                     => 0,
    'VARIABLETYPE_INTEGER'                     => 1,
    'VARIABLETYPE_FLOAT'                       => 2,
    'VARIABLETYPE_STRING'                      => 3,
    'IPS_KERNELSTARTED'                        => 10001,
    'KR_STARTING'                              => 10102,
    'KR_READY'                                 => 10103,
    'VM_UPDATE'                                => 10603,
    'IS_ACTIVE'                                => 102,
    'IS_INACTIVE'                              => 104
];
foreach ($constants as $constant => $value) {
    if (!defined($constant)) {
        define($constant, $value);
    }
}

/** @var array<int, array{type: int, value: mixed, action: bool}> $GLOBALS['OSBC_VARIABLES'] */
$GLOBALS['OSBC_VARIABLES'] = [];
/** @var array<int, array<string, int>> $GLOBALS['OSBC_IDENT_MAP'] */
$GLOBALS['OSBC_IDENT_MAP'] = [];
/** @var list<array{id: int, value: mixed}> $GLOBALS['OSBC_REQUEST_ACTIONS'] */
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_KERNEL_RUNLEVEL'] = KR_READY;
$GLOBALS['OSBC_NEXT_OBJECT_ID'] = 10000;

function IPS_GetKernelRunlevel(): int
{
    return $GLOBALS['OSBC_KERNEL_RUNLEVEL'];
}

function IPS_VariableExists(int $variableID): bool
{
    return isset($GLOBALS['OSBC_VARIABLES'][$variableID]);
}

/** @return array{VariableType: int, VariableAction: int, VariableCustomAction: int} */
function IPS_GetVariable(int $variableID): array
{
    return [
        'VariableType'         => $GLOBALS['OSBC_VARIABLES'][$variableID]['type'],
        'VariableAction'       => $GLOBALS['OSBC_VARIABLES'][$variableID]['action'] ? 5000 : 0,
        'VariableCustomAction' => 0
    ];
}

function HasAction(int $variableID): bool
{
    return IPS_VariableExists($variableID) && $GLOBALS['OSBC_VARIABLES'][$variableID]['action'];
}

function GetValue(int $variableID): mixed
{
    return $GLOBALS['OSBC_VARIABLES'][$variableID]['value'];
}

function SetValue(int $variableID, mixed $value): void
{
    $GLOBALS['OSBC_VARIABLES'][$variableID]['value'] = $value;
}

function RequestAction(int $variableID, mixed $value): bool
{
    if (!HasAction($variableID)) {
        throw new RuntimeException('RequestAction called without an available variable action.');
    }

    $GLOBALS['OSBC_REQUEST_ACTIONS'][] = ['id' => $variableID, 'value' => $value];

    return true;
}

class IPSModuleStrict
{
    public int $InstanceID = 4242;

    /** @var array<string, int> */
    public array $properties = [];
    /** @var array<string, int|float|bool|string> */
    public array $attributes = [];
    /** @var array<string, int> */
    public array $timers = [];
    /** @var array<int, array<int, bool>> */
    public array $messages = [];
    /** @var array<int, bool> */
    public array $references = [];
    /** @var array<string, array<string, mixed>> */
    public array $registeredVariables = [];
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

    public function RegisterAttributeString(string $name, string $default): void
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

    public function ReadAttributeString(string $name): string
    {
        return (string) $this->attributes[$name];
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

    public function WriteAttributeString(string $name, string $value): void
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

    /** @param array<string, mixed> $presentation */
    public function RegisterVariableInteger(
        string $ident,
        string $name,
        array $presentation,
        int $position = 0
    ): bool {
        return $this->RegisterTestVariable($ident, $name, VARIABLETYPE_INTEGER, $presentation, $position);
    }

    /** @param array<string, mixed> $presentation */
    public function RegisterVariableString(
        string $ident,
        string $name,
        array $presentation,
        int $position = 0
    ): bool {
        return $this->RegisterTestVariable($ident, $name, VARIABLETYPE_STRING, $presentation, $position);
    }

    public function SetValue(string $ident, mixed $value): void
    {
        $objectID = $GLOBALS['OSBC_IDENT_MAP'][$this->InstanceID][$ident];
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

    protected function RegisterReference(int $ID): bool
    {
        $this->references[$ID] = true;

        return true;
    }

    protected function UnregisterReference(int $ID): bool
    {
        unset($this->references[$ID]);

        return true;
    }

    /** @param array<string, mixed> $presentation */
    private function RegisterTestVariable(
        string $ident,
        string $name,
        int $type,
        array $presentation,
        int $position
    ): bool {
        $created = !isset($GLOBALS['OSBC_IDENT_MAP'][$this->InstanceID][$ident]);
        $objectID = $GLOBALS['OSBC_IDENT_MAP'][$this->InstanceID][$ident] ?? $GLOBALS['OSBC_NEXT_OBJECT_ID']++;
        $GLOBALS['OSBC_IDENT_MAP'][$this->InstanceID][$ident] = $objectID;
        $GLOBALS['OSBC_VARIABLES'][$objectID] ??= [
            'type'   => $type,
            'value'  => $type === VARIABLETYPE_STRING ? '' : 0,
            'action' => false
        ];
        $this->registeredVariables[$ident] = [
            'name'         => $name,
            'type'         => $type,
            'presentation' => $presentation,
            'position'     => $position,
            'id'           => $objectID
        ];

        return $created;
    }
}

require_once __DIR__ . '/../OpenShutterButtonControl/module.php';

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

/** @return list<int> */
function getReferenceIDs(IPSModuleStrict $module): array
{
    $references = array_keys($module->references);
    sort($references);

    return $references;
}

function configureModule(
    OpenShutterButtonControl $module,
    int $buttonID = 10,
    int $moveID = 20,
    int $positionID = 30
): void {
    $module->properties['ButtonID'] = $buttonID;
    $module->properties['MoveID'] = $moveID;
    $module->properties['PositionID'] = $positionID;
}

$GLOBALS['OSBC_VARIABLES'] = [
    10 => ['type' => VARIABLETYPE_BOOLEAN, 'value' => false, 'action' => false],
    11 => ['type' => VARIABLETYPE_STRING, 'value' => 'released', 'action' => false],
    20 => ['type' => VARIABLETYPE_STRING, 'value' => 'STOP', 'action' => true],
    21 => ['type' => VARIABLETYPE_STRING, 'value' => 'STOP', 'action' => true],
    22 => ['type' => VARIABLETYPE_STRING, 'value' => 'STOP', 'action' => false],
    30 => ['type' => VARIABLETYPE_INTEGER, 'value' => 50, 'action' => true],
    31 => ['type' => VARIABLETYPE_FLOAT, 'value' => 50.0, 'action' => true],
    32 => ['type' => VARIABLETYPE_INTEGER, 'value' => 50, 'action' => false],
    40 => ['type' => VARIABLETYPE_BOOLEAN, 'value' => false, 'action' => true]
];

$module = new OpenShutterButtonControl();
$module->Create();
assertSameValue(1000, $module->properties['ShortPressTime'], 'The default long-press threshold changed unexpectedly.');
assertTrue(!isset($module->properties['PositionUp']), 'Unused PositionUp property must not be registered.');
assertTrue(!isset($module->properties['PositionDown']), 'Unused PositionDown property must not be registered.');
assertTrue(isset($module->registeredVariables['last_duration_ms']), 'Duration status variable must be created in Create().');
assertTrue(isset($module->registeredVariables['last_action']), 'Action status variable must be created in Create().');
assertTrue(isset($module->messages[0][IPS_KERNELSTARTED]), 'Kernel-start message must be registered in Create().');
assertSameValue(0, $module->attributes['ActiveMoveID'], 'Active movement must be empty initially.');
assertSameValue('[]', $module->attributes['RegisteredReferences'], 'Reference attribute must be empty initially.');

$durationID = $GLOBALS['OSBC_IDENT_MAP'][4242]['last_duration_ms'];
$actionID = $GLOBALS['OSBC_IDENT_MAP'][4242]['last_action'];
$durationPresentation = $module->registeredVariables['last_duration_ms']['presentation'];
assertSameValue(' ms', $durationPresentation['SUFFIX'] ?? null, 'Duration presentation must use milliseconds.');
$actionPresentation = $module->registeredVariables['last_action']['presentation'];
assertSameValue(
    VARIABLE_PRESENTATION_VALUE_PRESENTATION,
    $actionPresentation['PRESENTATION'] ?? null,
    'Last action must use a native value presentation.'
);
$options = json_decode((string) ($actionPresentation['OPTIONS'] ?? ''), true, 512, JSON_THROW_ON_ERROR);
assertSameValue('short_press', $options[1]['Value'] ?? null, 'Short-press presentation option is missing.');
assertSameValue('long_press', $options[2]['Value'] ?? null, 'Long-press presentation option is missing.');

$module->ApplyChanges();
assertSameValue(209, $module->status, 'An incomplete configuration must set status 209.');

configureModule($module);
$module->ApplyChanges();
assertSameValue(IS_ACTIVE, $module->status, 'A valid configuration must activate the module.');
assertTrue(isset($module->messages[10][VM_UPDATE]), 'The button update message was not registered.');
assertSameValue(10, $module->attributes['RegisteredButtonID'], 'The registered button ID attribute is incorrect.');
assertSameValue([10, 20, 30], getReferenceIDs($module), 'Configured variables must be registered as references.');

// Short press while opening: position mode 0 means 0=open and 100=closed.
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->attributes['PressStart'] = microtime(true) - 0.2;
$GLOBALS['OSBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue([['id' => 30, 'value' => 0]], $GLOBALS['OSBC_REQUEST_ACTIONS'], 'Short opening press must target position 0.');
assertSameValue('short_press', GetValue($actionID), 'Short press status was not stored.');
assertTrue(GetValue($durationID) >= 150 && GetValue($durationID) <= 400, 'Short-press duration was not measured plausibly.');

// Duplicate release messages must not trigger another action.
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue(1, count($GLOBALS['OSBC_REQUEST_ACTIONS']), 'Duplicate release update triggered an extra action.');

// Long press while opening: OPEN on threshold, STOP on release.
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->RequestAction('HandleLongPress', 0);
assertSameValue(20, $module->attributes['ActiveMoveID'], 'The active movement variable must be persisted.');
$GLOBALS['OSBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue(
    [
        ['id' => 20, 'value' => 'OPEN'],
        ['id' => 20, 'value' => 'STOP']
    ],
    $GLOBALS['OSBC_REQUEST_ACTIONS'],
    'Long opening press must send OPEN followed by STOP.'
);
assertSameValue(0, $module->attributes['ActiveMoveID'], 'The active movement ID must be cleared after STOP.');
assertSameValue('long_press', GetValue($actionID), 'Long press status was not stored.');

// A delayed timer must still emit a complete long-press command sequence.
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->attributes['PressStart'] = microtime(true) - 1.2;
$GLOBALS['OSBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue(
    [
        ['id' => 20, 'value' => 'OPEN'],
        ['id' => 20, 'value' => 'STOP']
    ],
    $GLOBALS['OSBC_REQUEST_ACTIONS'],
    'Delayed timer fallback must send OPEN followed by STOP.'
);

// Closing with 0=open / 100=closed must target 100 after a short press.
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$module->properties['Direction'] = 1;
$GLOBALS['OSBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->attributes['PressStart'] = microtime(true) - 0.1;
$GLOBALS['OSBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue([['id' => 30, 'value' => 100]], $GLOBALS['OSBC_REQUEST_ACTIONS'], 'Short closing press must target position 100.');
$module->properties['Direction'] = 0;

// Float position variables must receive a value of the matching type.
$module->properties['PositionID'] = 31;
$module->ApplyChanges();
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_VARIABLES'][10]['value'] = true;
$module->MessageSink(time(), 10, VM_UPDATE, []);
$module->attributes['PressStart'] = microtime(true) - 0.1;
$GLOBALS['OSBC_VARIABLES'][10]['value'] = false;
$module->MessageSink(time(), 10, VM_UPDATE, []);
assertSameValue([['id' => 31, 'value' => 0.0]], $GLOBALS['OSBC_REQUEST_ACTIONS'], 'Float position variables must receive Float targets.');
$module->properties['PositionID'] = 30;
$module->ApplyChanges();

assertSameValue(true, invokePrivate($module, 'MapButtonState', 'pressed'), 'Text state pressed must map to true.');
assertSameValue(false, invokePrivate($module, 'MapButtonState', 'OFF'), 'Text state OFF must map to false.');
assertSameValue(null, invokePrivate($module, 'MapButtonState', 'toggle'), 'Unsupported text state must remain unknown.');
assertSameValue(true, invokePrivate($module, 'MapButtonState', 1), 'Numeric state 1 must map to true.');
assertSameValue(false, invokePrivate($module, 'MapButtonState', 0.0), 'Numeric state 0 must map to false.');

// Changing the selected button must unregister the old sender and update references.
$module->properties['ButtonID'] = 11;
$module->ApplyChanges();
assertTrue(!isset($module->messages[10][VM_UPDATE]), 'Old button registration was not removed.');
assertTrue(isset($module->messages[11][VM_UPDATE]), 'New button registration was not created.');
assertSameValue([11, 20, 30], getReferenceIDs($module), 'References were not updated after changing the button.');

// Reconfiguration during movement must STOP the old movement variable exactly once.
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_VARIABLES'][11]['value'] = 'pressed';
$module->MessageSink(time(), 11, VM_UPDATE, []);
$module->RequestAction('HandleLongPress', 0);
$module->properties['MoveID'] = 21;
$module->ApplyChanges();
assertSameValue(
    [
        ['id' => 20, 'value' => 'OPEN'],
        ['id' => 20, 'value' => 'STOP']
    ],
    $GLOBALS['OSBC_REQUEST_ACTIONS'],
    'ApplyChanges must stop the movement on the previously used movement variable.'
);
assertSameValue(0, $module->attributes['ActiveMoveID'], 'Reconfiguration must clear the active movement ID.');
assertSameValue([11, 21, 30], getReferenceIDs($module), 'References were not updated after changing the movement variable.');

// Invalid variables, missing actions and invalid properties must produce explicit states.
$module->properties['ButtonID'] = 99;
$module->ApplyChanges();
assertSameValue(201, $module->status, 'Missing button variable must set status 201.');
configureModule($module, 10, 30, 30);
$module->ApplyChanges();
assertSameValue(202, $module->status, 'Non-string movement variable must set status 202.');
configureModule($module, 10, 20, 40);
$module->ApplyChanges();
assertSameValue(203, $module->status, 'Non-numeric position variable must set status 203.');
configureModule($module, 10, 22, 30);
$module->ApplyChanges();
assertSameValue(204, $module->status, 'Movement variable without action must set status 204.');
configureModule($module, 10, 20, 32);
$module->ApplyChanges();
assertSameValue(205, $module->status, 'Position variable without action must set status 205.');
configureModule($module);
$module->properties['ShortPressTime'] = 99;
$module->ApplyChanges();
assertSameValue(206, $module->status, 'Invalid long-press threshold must set status 206.');
$module->properties['ShortPressTime'] = 1000;
$module->properties['Direction'] = 2;
$module->ApplyChanges();
assertSameValue(207, $module->status, 'Invalid direction must set status 207.');
$module->properties['Direction'] = 0;
$module->properties['PositionMode'] = 2;
$module->ApplyChanges();
assertSameValue(208, $module->status, 'Invalid position mode must set status 208.');
$module->properties['PositionMode'] = 0;
$module->properties['ButtonID'] = 0;
$module->ApplyChanges();
assertSameValue(209, $module->status, 'Incomplete configuration must set status 209.');

$invalidIdentThrown = false;
try {
    $module->RequestAction('Unknown', 0);
} catch (InvalidArgumentException) {
    $invalidIdentThrown = true;
}
assertTrue($invalidIdentThrown, 'Unknown RequestAction identifiers must throw an exception.');

// ApplyChanges before KR_READY must defer initialization until IPS_KERNELSTARTED.
$GLOBALS['OSBC_KERNEL_RUNLEVEL'] = KR_STARTING;
$startupModule = new OpenShutterButtonControl();
$startupModule->InstanceID = 5000;
$startupModule->Create();
configureModule($startupModule);
$startupModule->ApplyChanges();
assertSameValue(IS_INACTIVE, $startupModule->status, 'Module must remain inactive before KR_READY.');
assertTrue(!isset($startupModule->messages[10][VM_UPDATE]), 'Button messages must not be registered before KR_READY.');
assertSameValue([], getReferenceIDs($startupModule), 'Object references must not be registered before KR_READY.');
$GLOBALS['OSBC_KERNEL_RUNLEVEL'] = KR_READY;
$startupModule->MessageSink(time(), 0, IPS_KERNELSTARTED, []);
assertSameValue(IS_ACTIVE, $startupModule->status, 'Kernel start must initialize a valid module.');
assertTrue(isset($startupModule->messages[10][VM_UPDATE]), 'Kernel start must register the button message.');
assertSameValue([10, 20, 30], getReferenceIDs($startupModule), 'Kernel start must register object references.');
$startupModule->Destroy();

// Destroy during movement must send STOP and remove all registrations.
$destroyModule = new OpenShutterButtonControl();
$destroyModule->InstanceID = 6000;
$destroyModule->Create();
configureModule($destroyModule);
$destroyModule->ApplyChanges();
$GLOBALS['OSBC_REQUEST_ACTIONS'] = [];
$GLOBALS['OSBC_VARIABLES'][10]['value'] = true;
$destroyModule->MessageSink(time(), 10, VM_UPDATE, []);
$destroyModule->RequestAction('HandleLongPress', 0);
$destroyModule->Destroy();
assertSameValue(
    [
        ['id' => 20, 'value' => 'OPEN'],
        ['id' => 20, 'value' => 'STOP']
    ],
    $GLOBALS['OSBC_REQUEST_ACTIONS'],
    'Destroy must stop an active movement.'
);
assertTrue($destroyModule->destroyed, 'Parent Destroy() was not called.');
assertTrue(!isset($destroyModule->messages[10][VM_UPDATE]), 'Button registration must be removed during Destroy().');
assertTrue(!isset($destroyModule->messages[0][IPS_KERNELSTARTED]), 'Kernel registration must be removed during Destroy().');
assertSameValue([], getReferenceIDs($destroyModule), 'Object references must be removed during Destroy().');
assertSameValue(0, $destroyModule->timers['LongPress'], 'Long-press timer must be stopped during Destroy().');
assertSameValue(0, $destroyModule->attributes['ActiveMoveID'], 'Destroy must clear the active movement ID.');

// Static form constraints must mirror the runtime validation.
$form = json_decode(
    file_get_contents(__DIR__ . '/../OpenShutterButtonControl/form.json'),
    true,
    512,
    JSON_THROW_ON_ERROR
);
$elements = $form['elements'];
$buttonElement = $elements[1]['items'][0];
$moveElement = $elements[2]['items'][0];
$positionElement = $elements[2]['items'][1];
assertSameValue([0, 1, 2, 3], $buttonElement['validVariableTypes'] ?? null, 'Button type filter is incomplete.');
assertSameValue([3], $moveElement['validVariableTypes'] ?? null, 'Movement type filter must require String.');
assertSameValue(1, $moveElement['requiredAction'] ?? null, 'Movement selection must require an action.');
assertSameValue([1, 2], $positionElement['validVariableTypes'] ?? null, 'Position type filter must require Integer or Float.');
assertSameValue(1, $positionElement['requiredAction'] ?? null, 'Position selection must require an action.');
$statusCodes = array_column($form['status'], 'code');
assertSameValue([201, 202, 203, 204, 205, 206, 207, 208, 209], $statusCodes, 'Configuration status list is incomplete.');

// Restore and destroy the main instance cleanly.
configureModule($module);
$module->ApplyChanges();
$module->Destroy();
assertTrue($module->destroyed, 'Parent Destroy() was not called for the main module.');
assertSameValue([], getReferenceIDs($module), 'Main module references must be removed during Destroy().');

echo "OpenShutterButtonControl module regression tests passed.\n";
