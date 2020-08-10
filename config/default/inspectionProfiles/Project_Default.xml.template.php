<?php

declare(strict_types=1);

$xml = <<<'XML'
<component name="InspectionProjectProfileManager">
  <profile version="1.0">
    <option name="myName" value="Project Default" />
    <inspection_tool class="Eslint" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="InconsistentLineSeparators" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="LongLine" enabled="true" level="WARNING" enabled_by_default="true" />
    :phpCsFixerInspection
    <inspection_tool class="PhpConstantNamingConventionInspection" enabled="true" level="WARNING" enabled_by_default="true">
      <option name="m_minLength" value="0" />
      <option name="m_maxLength" value="0" />
    </inspection_tool>
    <inspection_tool class="PhpDocMissingThrowsInspection" enabled="false" level="WEAK WARNING" enabled_by_default="false" />
    <inspection_tool class="PhpDocRedundantThrowsInspection" enabled="false" level="WEAK WARNING" enabled_by_default="false" />
    <inspection_tool class="PhpFullyQualifiedNameUsageInspection" enabled="true" level="WEAK WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpIllegalPsrClassPathInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpMethodNamingConventionInspection" enabled="true" level="WARNING" enabled_by_default="false">
      <scope name="Project Files" level="WARNING" enabled="true">
        <option name="m_minLength" value="1" />
        <option name="m_maxLength" value="60" />
      </scope>
      <scope name="Tests" level="WARNING" enabled="true">
        <option name="m_regex" value="[a-z][_A-Za-z\d]*" />
        <option name="m_minLength" value="0" />
        <option name="m_maxLength" value="60" />
      </scope>
    </inspection_tool>
    <inspection_tool class="PhpMethodOrClassCallIsNotCaseSensitiveInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpMissingStrictTypesDeclarationInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpMultipleClassesDeclarationsInOneFile" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpNonCanonicalElementsOrderInspection" enabled="true" level="WEAK WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpPropertyNamingConventionInspection" enabled="true" level="WARNING" enabled_by_default="true">
      <option name="m_minLength" value="0" />
      <option name="m_maxLength" value="0" />
    </inspection_tool>
    <inspection_tool class="PhpShortOpenTagInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpTraditionalSyntaxArrayLiteralInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpUndefinedCallbackInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="PhpUnhandledExceptionInspection" enabled="false" level="WARNING" enabled_by_default="false" />
    <inspection_tool class="PhpVariableNamingConventionInspection" enabled="true" level="WARNING" enabled_by_default="true">
      <option name="m_regex" value="[a-z][A-Za-z\d]*" />
      <option name="m_minLength" value="0" />
      <option name="m_maxLength" value="0" />
    </inspection_tool>
    <inspection_tool class="PhpVariableVariableInspection" enabled="true" level="WARNING" enabled_by_default="true" />
    <inspection_tool class="ProblematicWhitespace" enabled="true" level="WARNING" enabled_by_default="true" />
  </profile>
</component>
XML;

if (isset($phpCsFixerConfigPath)) {
    $phpCsFixerInspection = <<<'INSPECTION'
<inspection_tool class="PhpCSFixerValidationInspection" enabled="true" level="WEAK WARNING" enabled_by_default="true">
  <option name="CODING_STANDARD" value="Custom" />
  <option name="CUSTOM_RULESET_PATH" value="$PROJECT_DIR$/:phpCsFixerConfigPath" />
  <option name="ALLOW_RISKY_RULES" value="true" />
</inspection_tool>
INSPECTION;

    $phpCsFixerInspection = strtr($phpCsFixerInspection, [':phpCsFixerConfigPath' => $phpCsFixerConfigPath]);

} else {
    $phpCsFixerInspection = '';
}

echo strtr($xml, [':phpCsFixerInspection' => $phpCsFixerInspection]);
