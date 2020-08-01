<?php
declare(strict_types=1);

$xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  :phpCsFixerConfig
  <component name="PhpIncludePathManager">
    <include_path>
      <path value="$PROJECT_DIR$/vendor" />
    </include_path>
  </component>
  :interpreter
  <component name="PhpProjectSharedConfiguration" php_language_level="7" />
  <component name="PhpUnit">
    <phpunit_settings>
      <phpunit_by_interpreter interpreter_id="a91e239a-bac5-4e38-a9ca-7f394e1cfd37" load_method="CUSTOM_LOADER" configuration_file_path="/opt/project/phpunit.xml.dist" custom_loader_path="/opt/project/vendor/autoload.php" phpunit_phar_path="" use_configuration_file="true" />
      <PhpUnitSettings load_method="CUSTOM_LOADER" configuration_file_path="$PROJECT_DIR$/phpunit.xml.dist" custom_loader_path="$PROJECT_DIR$/vendor/autoload.php" use_configuration_file="true" />
    </phpunit_settings>
  </component>
</project>
XML;

if (isset($dockerImage)) {
    $interpreter = <<<'INTERPRETER'
<component name="PhpInterpreters">
    <interpreters>
      <interpreter home="docker://:dockerImage" id="a91e239a-bac5-4e38-a9ca-7f394e1cfd37" name=":dockerImage" debugger_id="php.debugger.XDebug">
        <remote_data DOCKER_ACCOUNT_NAME="Docker" DOCKER_IMAGE_NAME=":dockerImage" DOCKER_REMOTE_PROJECT_PATH="/opt/project" INTERPRETER_PATH="php" HELPERS_PATH="/opt/.phpstorm_helpers" INITIALIZED="false" VALID="true" />
      </interpreter>
    </interpreters>
</component>
INTERPRETER;

    $interpreter = strtr($interpreter, [':dockerImage' => $dockerImage]);

} else {
    $interpreter = '';
}

if (isset($phpCsFixerConfigPath) && isset($phpCsFixerExecutable)) {
    $phpCsFixerConfig = <<<'FIXER'
<component name="PhpCSFixer">
  <phpcsfixer_settings>
    <PhpCSFixerConfiguration standards="PSR1;PSR2;Symfony;DoctrineAnnotation;PHP70Migration;PHP71Migration" tool_path=":executable" />
  </phpcsfixer_settings>
</component>
FIXER;

    $phpCsFixerConfig = strtr($phpCsFixerConfig, [
        ':executable' => $phpCsFixerExecutable,
    ]);

} else {
    $phpCsFixerConfig = '';
}

echo strtr(
    $xml,
    [
        ':interpreter' => $interpreter,
        ':phpCsFixerConfig' => $phpCsFixerConfig,
    ]
);
