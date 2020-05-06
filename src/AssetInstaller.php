<?php
namespace Sapistudio\AssetManager;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Composer\Installer\PackageEvent;
use Composer\Script\CommandEvent;
use Composer\Util\Filesystem;
use Composer\Package\BasePackage;


use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;


class AssetInstaller extends LibraryInstaller
{
    const DEFAULT_ASSET_FOLDER  = 'vendorAssets';
    const EXTRA_ASSETS_PATH     = 'assetsPath';
    protected $assetConfiguration;
    protected $assetsFolderPath = null;
    protected $configComposer;
    protected $filesystem;
    protected $rules;
    
    public function __construct(IOInterface $io,Composer $composer){
        $this->filesystem       = new Filesystem();
        $extraConfig            = $composer->getPackage()->getExtra();
        $this->assetsFolderPath = (isset($extraConfig[self::EXTRA_ASSETS_PATH])) ? $extraConfig[self::EXTRA_ASSETS_PATH] : realpath(getcwd()).DIRECTORY_SEPARATOR.self::DEFAULT_ASSET_FOLDER;
        $this->filesystem->ensureDirectoryExists($this->assetsFolderPath);
        parent::__construct($io,$composer);
    }
    
    public function getInstallPath(PackageInterface $package)
    {
        $pattern = $this->getPluginConfiguration()->getPattern($package);
        if ($pattern) {
            $basePath   = $this->buildPath(rtrim($this->assetsFolderPath,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.ltrim($pattern,DIRECTORY_SEPARATOR), $this->getPackageReplacementTokens($package));
            $targetDir  = $package->getTargetDir();
            $dsads = $basePath . ($targetDir ? '/'.$targetDir : '');
        } else {
            $dsads =  parent::getInstallPath($package);
        }
        
        
        print_R($dsads);
        die();
        return parent::getInstallPath($package);
    }

    public function supports($packageType)
    {
        return $this->getPluginConfiguration()->isPackageTypeSupported($packageType);
    }

    protected function getPackageReplacementTokens(PackageInterface $package)
    {
        $vars       = ['{$type}' => $package->getType()];
        $prettyName = $package->getPrettyName();
        $pieces     = ['{$vendor}' => '','{$name}' => $prettyName];
        if (strpos($prettyName, '/') !== false){
            $pieces = array_combine(['{$vendor}','{$name}'],explode('/', $prettyName,2));
        }
        return array_merge($vars, $pieces);
    }

    protected function buildPath($pattern, $tokens = [])
    {
        return strtr($pattern, $tokens);
    }

    protected function getPluginConfiguration()
    {
        if (!isset($this->assetConfiguration)) {
            $extra = $this->composer->getPackage()->getExtra();
            $this->assetConfiguration = new AssetConfiguration($extra);
        }
        return $this->assetConfiguration;
    }
}
