<?php

declare(strict_types=1);

use Paysera\PhpStormHelper\TemplateControl;

if (!isset($symfonyEnabled) || !$symfonyEnabled) {
    return TemplateControl::SKIP;
}

echo <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<project version="4">
  <component name="Symfony2PluginSettings">
    <option name="serviceJsNameStrategy" value="var className = args.className;&#10;var parts = className.match(/^(.*\wBundle)\\(\w+)\\([^\\]+\\)*(\w+)$/);&#10;if (!parts) {&#10;    return args.defaultNaming;&#10;}&#10;&#10;var bundleName = parts[1].replace(/\\|Bundle/g, '');&#10;var namespace = parts[2];&#10;var serviceName = parts[4];&#10;&#10;if (['Command', 'Controller', 'Denormalizer', 'Listener', 'Normalizer', 'Processor', 'Repository', 'Worker'].indexOf(namespace) !== -1) {&#10;    if (serviceName.substring(serviceName.length - namespace.length) === namespace) {&#10;        serviceName = serviceName.substring(0, serviceName.length - namespace.length);&#10;    }&#10;    namespace += '.';&#10;} else if (namespace === 'Service') {&#10;    namespace = '';&#10;} else {&#10;    namespace += '.';&#10;}&#10;&#10;return camelCaseToSnake(bundleName + '.' + namespace + serviceName);&#10;&#10;function camelCaseToSnake(str) {&#10;      return str&#10;        .replace(/[^a-zA-Z0-9.]+/g, '_')&#10;        .replace(/([A-Z]+)([A-Z][a-z])/g, '$1_$2')&#10;        .replace(/([a-z])([A-Z])/g, '$1_$2')&#10;        .replace(/([0-9])([^0-9.])/g, '$1_$2')&#10;        .replace(/([^0-9.])([0-9])/g, '$1_$2')&#10;        .replace(/-+/g, '_')&#10;        .toLowerCase();&#10;}&#10;" />
    <option name="pluginEnabled" value="true" />
    <option name="lastServiceGeneratorLanguage" value="xml" />
  </component>
</project>
XML;
