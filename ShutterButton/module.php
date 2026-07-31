<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/helper/VariablePresentationHelper.php';

use Burki24\SymconModuleHelper\VariablePresentationHelper;

class ShutterButton extends IPSModuleStrict
{
    use VariablePresentationHelper;

    private const DIRECTION_UP = 0;
    private const DIRECTION_DOWN = 1;
    private const POSITION_MODE_ZERO_OPEN = 0;
    private const POSITION_MODE_HUNDRED_OPEN = 1;

    /**
     * Registers properties, attributes and the long-press timer.
     */
    public function Create(): void
    {
        parent::Create();

        $this->RegisterPropertyInteger('ButtonID', 0);
        $this->RegisterPropertyInteger('MoveID', 0);
        $this->RegisterPropertyInteger('PositionID', 0);
        $this->RegisterPropertyInteger('Direction', self::DIRECTION_UP);
        $this->RegisterPropertyInteger('ShortPressTime', 1000);
        $this->RegisterPropertyInteger('PositionMode', self::POSITION_MODE_ZERO_OPEN);

        $this->RegisterAttributeFloat('PressStart', 0.0);
        $this->RegisterAttributeBoolean('ButtonPressed', false);
        $this->RegisterAttributeBoolean('LongPressActive', false);
        $this->RegisterAttributeInteger('RegisteredButtonID', 0);

        $this->RegisterTimer(
            'LongPress',
            0,
            'IPS_RequestAction($_IPS[\'TARGET\'], \'HandleLongPress\', 0);'
        );
    }

    /**
     * Applies the configuration and registers the selected button variable.
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();

        $this->MigrateLegacyStatusVariables();
        $this->MaintainStatusVariables();
        $this->ResetPressState();
        $this->UpdateButtonRegistration();

        $this->SetStatus($this->DetermineConfigurationStatus());
    }

    /**
     * Removes message registrations and stops the timer before deletion.
     */
    public function Destroy(): void
    {
        $this->SetTimerInterval('LongPress', 0);
        $this->UnregisterConfiguredButton();

        parent::Destroy();
    }

    /**
     * Handles updates from the configured button variable.
     *
     * @param int          $TimeStamp Unix timestamp supplied by Symcon.
     * @param int          $SenderID  Sender object ID.
     * @param int          $Message   Symcon message ID.
     * @param array<mixed> $Data      Message payload.
     */
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        if ($Message !== VM_UPDATE || $SenderID !== $this->ReadPropertyInteger('ButtonID')) {
            return;
        }

        $this->HandleButton();
    }

    /**
     * Handles the internal long-press timer action.
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($Ident !== 'HandleLongPress') {
            throw new InvalidArgumentException('Invalid Ident: ' . $Ident);
        }

        $this->HandleLongPress();
    }

    /**
     * Evaluates the current button value and handles press/release transitions.
     */
    private function HandleButton(): void
    {
        if ($this->DetermineConfigurationStatus() !== IS_ACTIVE) {
            return;
        }

        $buttonID = $this->ReadPropertyInteger('ButtonID');
        $rawValue = GetValue($buttonID);
        $pressed = $this->MapButtonState($rawValue);

        $this->SendDebug('ButtonRaw', json_encode($rawValue, JSON_UNESCAPED_UNICODE), 0);

        if ($pressed === null) {
            $this->SendDebug('Button', 'Unsupported button value: ' . json_encode($rawValue, JSON_UNESCAPED_UNICODE), 0);
            return;
        }

        if ($pressed) {
            $this->HandleButtonPressed();
            return;
        }

        $this->HandleButtonReleased();
    }

    /**
     * Starts short/long-press timing for a new press transition.
     */
    private function HandleButtonPressed(): void
    {
        if ($this->ReadAttributeBoolean('ButtonPressed')) {
            return;
        }

        $this->WriteAttributeBoolean('ButtonPressed', true);
        $this->WriteAttributeBoolean('LongPressActive', false);
        $this->WriteAttributeFloat('PressStart', microtime(true));
        $this->SetTimerInterval('LongPress', $this->ReadPropertyInteger('ShortPressTime'));

        $this->SendDebug('Button', 'Pressed', 0);
    }

    /**
     * Finishes a press transition and executes the matching shutter action.
     */
    private function HandleButtonReleased(): void
    {
        if (!$this->ReadAttributeBoolean('ButtonPressed')) {
            return;
        }

        $this->SetTimerInterval('LongPress', 0);
        $this->WriteAttributeBoolean('ButtonPressed', false);

        $pressStart = $this->ReadAttributeFloat('PressStart');
        $duration = max(0, (int) round((microtime(true) - $pressStart) * 1000));
        $this->SetValue('last_duration_ms', $duration);

        if ($this->ReadAttributeBoolean('LongPressActive')) {
            $this->WriteAttributeBoolean('LongPressActive', false);
            $this->SetValue('last_action', 'long_press');
            $this->StopShutter();
            return;
        }

        if ($duration < $this->ReadPropertyInteger('ShortPressTime')) {
            $this->SetValue('last_action', 'short_press');
            $this->HandleShortPress();
            return;
        }

        // The timer should normally have fired already. This fallback keeps the
        // long-press behavior deterministic if timer execution was delayed.
        $this->HandleLongPress();
        $this->WriteAttributeBoolean('LongPressActive', false);
        $this->SetValue('last_action', 'long_press');
        $this->StopShutter();
    }

    /**
     * Starts continuous shutter movement after the long-press threshold.
     */
    private function HandleLongPress(): void
    {
        $this->SetTimerInterval('LongPress', 0);

        if (!$this->ReadAttributeBoolean('ButtonPressed') || $this->ReadAttributeBoolean('LongPressActive')) {
            return;
        }

        $this->WriteAttributeBoolean('LongPressActive', true);
        $this->SetValue('last_action', 'long_press');
        $this->MoveShutter();
    }

    /**
     * Moves the shutter to the configured end position after a short press.
     */
    private function HandleShortPress(): void
    {
        $positionID = $this->ReadPropertyInteger('PositionID');
        $direction = $this->ReadPropertyInteger('Direction');
        $positionMode = $this->ReadPropertyInteger('PositionMode');

        $openPosition = $positionMode === self::POSITION_MODE_HUNDRED_OPEN ? 100 : 0;
        $closedPosition = $positionMode === self::POSITION_MODE_HUNDRED_OPEN ? 0 : 100;
        $targetPosition = $direction === self::DIRECTION_UP ? $openPosition : $closedPosition;

        $this->SendDebug('Shutter', 'Set position to ' . $targetPosition, 0);
        RequestAction($positionID, $targetPosition);
    }

    /**
     * Starts continuous movement in the configured direction.
     */
    private function MoveShutter(): void
    {
        $direction = $this->ReadPropertyInteger('Direction');
        $command = $direction === self::DIRECTION_UP ? 'OPEN' : 'CLOSE';

        $this->SendDebug('Shutter', 'Move command: ' . $command, 0);
        RequestAction($this->ReadPropertyInteger('MoveID'), $command);
    }

    /**
     * Stops continuous shutter movement.
     */
    private function StopShutter(): void
    {
        $this->SendDebug('Shutter', 'Move command: STOP', 0);
        RequestAction($this->ReadPropertyInteger('MoveID'), 'STOP');
    }

    /**
     * Maps common boolean, numeric and textual button states.
     */
    private function MapButtonState(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return match ((float) $value) {
                1.0     => true,
                0.0     => false,
                default => null
            };
        }

        if (!is_string($value)) {
            return null;
        }

        return match (strtolower(trim($value))) {
            'pressed', 'press', 'down', 'on', 'true', '1'    => true,
            'released', 'release', 'up', 'off', 'false', '0' => false,
            default                                          => null
        };
    }

    /**
     * Creates the read-only status variables with native Symcon presentations.
     */
    private function MaintainStatusVariables(): void
    {
        $this->MaintainVariable(
            'last_duration_ms',
            $this->Translate('Last press duration'),
            VARIABLETYPE_INTEGER,
            $this->IntegerPresentation('ms', 0, 60000),
            10,
            true
        );
        $this->MaintainVariable(
            'last_action',
            $this->Translate('Last action'),
            VARIABLETYPE_STRING,
            $this->OptionsPresentation([
                [
                    'Value'   => '',
                    'Caption' => $this->Translate('No action yet'),
                    'Color'   => 0x808080
                ],
                [
                    'Value'   => 'short_press',
                    'Caption' => $this->Translate('Short press'),
                    'Color'   => 0x00AA00
                ],
                [
                    'Value'   => 'long_press',
                    'Caption' => $this->Translate('Long press'),
                    'Color'   => 0x0066CC
                ]
            ]),
            20,
            true
        );
    }

    /**
     * Renames the two status variables from the original release in place.
     */
    private function MigrateLegacyStatusVariables(): void
    {
        $this->MigrateLegacyVariableIdent('LastDuration', 'last_duration_ms');
        $this->MigrateLegacyVariableIdent('LastAction', 'last_action');

        $lastActionID = @IPS_GetObjectIDByIdent('last_action', $this->InstanceID);
        if ($lastActionID === false) {
            return;
        }

        $legacyValue = GetValue($lastActionID);
        if (!is_string($legacyValue)) {
            return;
        }

        $normalizedValue = match ($legacyValue) {
            'ShortPress' => 'short_press',
            'LongPress'  => 'long_press',
            default      => $legacyValue
        };
        if ($normalizedValue !== $legacyValue) {
            SetValue($lastActionID, $normalizedValue);
        }
    }

    /**
     * Renames a legacy variable ident if the new ident is not yet present.
     */
    private function MigrateLegacyVariableIdent(string $legacyIdent, string $newIdent): void
    {
        $legacyID = @IPS_GetObjectIDByIdent($legacyIdent, $this->InstanceID);
        if ($legacyID === false || @IPS_GetObjectIDByIdent($newIdent, $this->InstanceID) !== false) {
            return;
        }

        IPS_SetIdent($legacyID, $newIdent);
    }

    /**
     * Replaces the previous button-message registration with the current one.
     */
    private function UpdateButtonRegistration(): void
    {
        $this->UnregisterConfiguredButton();

        if ($this->DetermineConfigurationStatus() !== IS_ACTIVE) {
            return;
        }

        $buttonID = $this->ReadPropertyInteger('ButtonID');
        $this->RegisterMessage($buttonID, VM_UPDATE);
        $this->WriteAttributeInteger('RegisteredButtonID', $buttonID);
    }

    /**
     * Removes the message registration stored in the instance attribute.
     */
    private function UnregisterConfiguredButton(): void
    {
        $registeredButtonID = $this->ReadAttributeInteger('RegisteredButtonID');
        if ($registeredButtonID > 0) {
            $this->UnregisterMessage($registeredButtonID, VM_UPDATE);
        }

        $this->WriteAttributeInteger('RegisteredButtonID', 0);
    }

    /**
     * Resets timer and transition state without triggering a shutter command.
     */
    private function ResetPressState(): void
    {
        $this->SetTimerInterval('LongPress', 0);
        $this->WriteAttributeFloat('PressStart', 0.0);
        $this->WriteAttributeBoolean('ButtonPressed', false);
        $this->WriteAttributeBoolean('LongPressActive', false);
    }

    /**
     * Validates all configured variables and returns the matching module status.
     */
    private function DetermineConfigurationStatus(): int
    {
        $buttonID = $this->ReadPropertyInteger('ButtonID');
        $moveID = $this->ReadPropertyInteger('MoveID');
        $positionID = $this->ReadPropertyInteger('PositionID');

        if ($buttonID <= 0 || $moveID <= 0 || $positionID <= 0) {
            return IS_INACTIVE;
        }

        if (!$this->VariableHasType($buttonID, [VARIABLETYPE_BOOLEAN, VARIABLETYPE_INTEGER, VARIABLETYPE_FLOAT, VARIABLETYPE_STRING])) {
            return 201;
        }
        if (!$this->VariableHasType($moveID, [VARIABLETYPE_STRING])) {
            return 202;
        }
        if (!$this->VariableHasType($positionID, [VARIABLETYPE_INTEGER, VARIABLETYPE_FLOAT])) {
            return 203;
        }

        return IS_ACTIVE;
    }

    /**
     * Checks whether a variable exists and has one of the expected types.
     *
     * @param list<int> $allowedTypes
     */
    private function VariableHasType(int $variableID, array $allowedTypes): bool
    {
        if (!IPS_VariableExists($variableID)) {
            return false;
        }

        $variable = IPS_GetVariable($variableID);

        return in_array($variable['VariableType'], $allowedTypes, true);
    }
}
