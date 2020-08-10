<?php

declare(strict_types=1);

use Paysera\PhpStormHelper\TemplateControl;

$xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  <component name="ProjectModuleManager">
    <modules>
      <module fileurl="file://$PROJECT_DIR$/.idea/:projectName.iml" filepath="$PROJECT_DIR$/.idea/:projectName.iml" />
    </modules>
  </component>
</project>
XML;

if (!isset($projectName)) {
    return TemplateControl::SKIP;
}

echo strtr($xml, [':projectName' => $projectName]);
