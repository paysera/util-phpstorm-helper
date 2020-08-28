<?php

declare(strict_types=1);

namespace Paysera\PhpStormHelper\Entity;

/**
 * @api
 */
class SuggestedGlobalConfiguration extends GlobalConfiguration
{
    public function __construct()
    {
        parent::__construct();

        $this
            ->setExternalToolConfigurations([
                (new ExternalToolConfiguration())
                    ->setName('Fix CS (debug)')
                    ->setCommand('bin/paysera-php-cs-fixer')
                    ->setParameters('fix --diff --verbose --config=$ProjectFileDir$/.php_cs --dry-run "$FilePath$"')
                    ->setSynchronizationRequired(false)
                    ->setConsoleShownAlways(true),
                (new ExternalToolConfiguration())
                    ->setName('Fix CS (safe)')
                    ->setCommand('bin/paysera-php-cs-fixer')
                    ->setParameters('fix --config=$ProjectFileDir$/.php_cs_safe "$FilePath$"'),
                (new ExternalToolConfiguration())
                    ->setName('Fix CS (risky)')
                    ->setCommand('bin/paysera-php-cs-fixer')
                    ->setParameters('fix --config=$ProjectFileDir$/.php_cs_risky "$FilePath$"'),
                (new ExternalToolConfiguration())
                    ->setName('Fix CS (everything)')
                    ->setCommand('bin/paysera-php-cs-fixer')
                    ->setParameters('fix --config=$ProjectFileDir$/.php_cs "$FilePath$"'),
            ])
            ->setPlugins([
                'https://plugins.jetbrains.com/files/7320/50706/PHP_Annotations-5.3.3.zip',
                'https://plugins.jetbrains.com/files/7219/60266/Symfony_Plugin-0.17.172.zip',
                'https://plugins.jetbrains.com/files/7495/48036/idea-gitignore-3.0.0.141.zip',
                'https://plugins.jetbrains.com/files/9525/49193/dotenv.jar',
                'https://plugins.jetbrains.com/files/4230/60154/bashsupport-1.7.7.zip',
                'https://plugins.jetbrains.com/files/8459/37865/raml-plugin-0.13.zip',
                'https://plugins.jetbrains.com/files/7792/35585/intellij-ansible-0.9.5.zip',
            ])
        ;
    }
}
