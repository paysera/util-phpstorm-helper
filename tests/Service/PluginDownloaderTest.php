<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Tests\Service;

use Alchemy\Zippy\Archive\ArchiveInterface;
use Alchemy\Zippy\Zippy;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Paysera\PhpStormHelper\Service\DirectoryResolver;
use Paysera\PhpStormHelper\Service\PluginDownloader;
use Symfony\Component\Filesystem\Filesystem;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class PluginDownloaderTest extends MockeryTestCase
{
    public function testDownloadPluginsWithZip()
    {
        $url = 'https://plugins.jetbrains.com/files/7219/59728/Some_plugin-abc-0.17.171.zip?updateId=60154&param=value';

        $directoryResolver = Mockery::mock(DirectoryResolver::class);
        $directoryResolver->expects('getPluginDirectory')->andReturn('/dir/plugins');

        $archive = Mockery::mock(ArchiveInterface::class)
            ->expects('extract')
            ->with('/dir/plugins')
            ->getMock()
        ;
        $zippy = Mockery::mock(Zippy::class);
        $zippy
            ->expects('open')
            ->with('/dir/plugins/Some plugin-abc.zip')
            ->andReturn($archive)
        ;

        $response = new Response(200, [], 'response');
        $client = Mockery::mock(ClientInterface::class);
        $client
            ->expects('request')
            ->with('GET', $url)
            ->andReturn($response)
        ;

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem
            ->expects('dumpFile')
            ->with('/dir/plugins/Some plugin-abc.zip', 'response')
        ;
        $filesystem
            ->expects('remove')
            ->with('/dir/plugins/Some plugin-abc.zip')
        ;

        $pluginDownloader = new PluginDownloader($directoryResolver, $zippy, $client, $filesystem);
        $pluginDownloader->downloadPlugins([$url]);
    }

    public function testDownloadPluginsWithJar()
    {
        $url = 'https://plugins.jetbrains.com/files/9525/49193/dotenv.jar';

        $directoryResolver = Mockery::mock(DirectoryResolver::class);
        $directoryResolver->expects('getPluginDirectory')->andReturn('/dir/plugins');

        $zippy = Mockery::mock(Zippy::class);

        $response = new Response(200, [], 'response');
        $client = Mockery::mock(ClientInterface::class);
        $client
            ->expects('request')
            ->with('GET', $url)
            ->andReturn($response)
        ;

        $filesystem = Mockery::mock(Filesystem::class);
        $filesystem
            ->expects('dumpFile')
            ->with('/dir/plugins/dotenv.jar', 'response')
        ;

        $pluginDownloader = new PluginDownloader($directoryResolver, $zippy, $client, $filesystem);
        $pluginDownloader->downloadPlugins([$url]);
    }
}
