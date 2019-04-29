<?php
declare(strict_types=1);

use Paysera\PhpStormHelper\TemplateControl;

if (!isset($dockerImage)) {
    return TemplateControl::SKIP;
}

echo <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  <component name="PhpDockerContainerSettings">
    <list>
      <map>
        <entry key="a91e239a-bac5-4e38-a9ca-7f394e1cfd37">
          <value>
            <DockerContainerSettings>
              <option name="version" value="1" />
              <option name="volumeBindings">
                <list>
                  <DockerVolumeBindingImpl>
                    <option name="containerPath" value="/opt/project" />
                    <option name="hostPath" value="$PROJECT_DIR$" />
                  </DockerVolumeBindingImpl>
                </list>
              </option>
            </DockerContainerSettings>
          </value>
        </entry>
      </map>
    </list>
  </component>
</project>
XML;
