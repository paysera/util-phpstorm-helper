<?php

declare(strict_types=1);

$xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  <component name="JavaScriptSettings">
    <option name="languageLevel" value="JSX" />
  </component>
  :webpackConfig
</project>
XML;

if (isset($webpackConfigPath)) {
    $webpackConfig = <<<'CONFIG'
<component name="WebPackConfiguration">
    <option name="path" value="$PROJECT_DIR$/:path" />
</component>
CONFIG;

    $webpackConfig = strtr($webpackConfig, [':path' => $webpackConfigPath]);
} else {
    $webpackConfig = '';
}

echo strtr($xml, [':webpackConfig' => $webpackConfig]);
