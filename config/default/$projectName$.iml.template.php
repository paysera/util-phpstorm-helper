<?php

declare(strict_types=1);

use Paysera\PhpStormHelper\Entity\SourceFolder;

$xml = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<module type="WEB_MODULE" version="4">
  <component name="NewModuleRootManager">
    <content url="file://$MODULE_DIR$">
      :sourceAndExcludeFolders
    </content>
    <orderEntry type="inheritedJdk" />
    <orderEntry type="sourceFolder" forTests="false" />
  </component>
</module>
XML;

$elements = [];
/** @var SourceFolder[] $sourceFolders */
foreach ($sourceFolders ?? [] as $folder) {
    if ($folder->getType() === SourceFolder::TYPE_EXCLUDED) {
        $elements[] = sprintf('<excludeFolder url="file://$MODULE_DIR$/%s" />', $folder->getPath());
    } else {
        $elements[] = sprintf(
            '<sourceFolder url="file://$MODULE_DIR$/%s" isTestSource="%s"%s />',
            $folder->getPath(),
            $folder->getType() === SourceFolder::TYPE_TEST_SOURCE ? 'true' : 'false',
            $folder->getPackagePrefix() !== null
                ? sprintf(' packagePrefix="%s"', $folder->getPackagePrefix())
                : ''
        );
    }
}

echo strtr($xml, [':sourceAndExcludeFolders' => implode("\n      ", $elements)]);
