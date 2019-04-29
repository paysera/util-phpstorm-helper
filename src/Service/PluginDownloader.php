<?php
declare(strict_types=1);

namespace Paysera\PhpStormHelper\Service;

use RuntimeException;
use Alchemy\Zippy\Zippy;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Filesystem\Filesystem;

class PluginDownloader
{
    private $directoryResolver;
    private $zippy;
    private $client;
    private $filesystem;

    public function __construct(
        DirectoryResolver $directoryResolver,
        Zippy $zippy,
        ClientInterface $client,
        Filesystem $filesystem
    ) {
        $this->directoryResolver = $directoryResolver;
        $this->zippy = $zippy;
        $this->client = $client;
        $this->filesystem = $filesystem;
    }

    public function downloadPlugins(array $plugins)
    {
        $pluginDirectory = $this->directoryResolver->getPluginDirectory();

        foreach ($plugins as $pluginUrl) {
            $this->ensurePluginInstalled($pluginDirectory, $pluginUrl);
        }
    }

    private function ensurePluginInstalled(string $pluginDirectory, string $pluginUrl)
    {
        $path = parse_url($pluginUrl, PHP_URL_PATH);
        $filename = basename($path);
        $parts = explode('.', $filename);
        if ($parts < 2) {
            throw new RuntimeException(sprintf('Unexpected filename (%s) for plugin %s', $filename, $pluginUrl));
        }
        $extension = $parts[count($parts) - 1];
        if (!in_array($extension, ['jar', 'zip'], true)) {
            throw new RuntimeException(sprintf('Unexpected extension (%s) for plugin %s', $extension, $pluginUrl));
        }

        $pluginName = $this->resolvePluginName($extension, $filename);
        $pluginFullPath = $pluginDirectory . '/' . $pluginName;
        if (file_exists($pluginFullPath)) {
            return;
        }

        $this->downloadPlugin($pluginUrl, $extension, $pluginFullPath);
    }

    private function resolvePluginName(string $extension, string $filename): string
    {
        if ($extension !== 'zip') {
            return $filename;
        }

        $parts = explode('-', $filename);
        if (count($parts) < 2) {
            throw new RuntimeException(sprintf('Unexpected plugin filename %s', $filename));
        }
        unset($parts[count($parts) - 1]);
        $name = implode('-', $parts);

        return str_replace('_', ' ', $name);
    }

    private function downloadPlugin(string $pluginUrl, string $extension, string $pluginFullPath)
    {
        $response = $this->client->request('GET', $pluginUrl);
        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException(sprintf('Unexpected status code %s', $response->getStatusCode()));
        }

        if ($extension !== 'zip') {
            $this->filesystem->dumpFile($pluginFullPath, $response->getBody()->getContents());
            return;
        }

        $zipFullPath = $pluginFullPath . '.zip';
        $this->filesystem->dumpFile($zipFullPath, $response->getBody()->getContents());
        $this->zippy->open($zipFullPath)->extract(dirname($pluginFullPath));
        $this->filesystem->remove($zipFullPath);
    }
}
