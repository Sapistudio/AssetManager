<?php
namespace Sapistudio\AssetManager;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;



use Composer\EventDispatcher\EventSubscriberInterface;

use Composer\Installer\PackageEvents;
use Composer\Script\CommandEvent;
use Composer\Util\Filesystem;
use Composer\Package\BasePackage;


class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $composer->getInstallationManager()->addInstaller(new AssetInstaller($io, $composer));
    }
    
    public static function getSubscribedEvents()
	{
	   die('dasdasdadas');
	   return [
            PackageEvents ::PRE_FILE_DOWNLOAD  => 'onPostPackageInstall',
            PackageEvents ::POST_PACKAGE_UPDATE => 'onPostPackageUpdate',
            PackageEvents ::POST_PACKAGE_UNINSTALL => 'onPostPackageUninstall',
		];
	}
    
    /**
     * Function to run after a package has been installed
     */
    public function onPostPackageInstall(PackageEvent $event)
    {
        /** @var \Composer\Package\CompletePackage $package */
        $this->cleanPackage($event->getOperation()->getTargetPackage());
    }

    /**
     * Function to run after a package has been updated
     */
    public function onPostPackageUpdate(PackageEvent $event)
    {
        /** @var \Composer\Package\CompletePackage $package */
        $this->cleanPackage($event->getOperation()->getTargetPackage());
    }

    /**
     * Function to run after a package has been updated
     *
     * @param CommandEvent $event
     */
    public function onPostPackageUninstall(PackageEvent $event)
    {
        /** @var \Composer\Package\CompletePackage $package */
        $this->cleanPackage($event->getOperation()->getTargetPackage());
    }

    /**
     * Clean a package, based on its rules.
     *
     * @param BasePackage  $package  The package to clean
     * @return bool True if cleaned
     */
    protected function cleanPackage(BasePackage $package)
    {
        print_R($package->getExtra());
        return;
        // Only clean 'dist' packages
        if ($package->getInstallationSource() !== 'dist') {
            return false;
        }

        $vendorDir = $this->config->get('vendor-dir');
        $targetDir = $package->getTargetDir();
        $packageName = $package->getPrettyName();
        $packageDir = $targetDir ? $packageName . '/' . $targetDir : $packageName ;

        $rules = isset($this->rules[$packageName]) ? $this->rules[$packageName] : null;
        if(!$rules){
            return;
        }

        $dir = $this->filesystem->normalizePath(realpath($vendorDir . '/' . $packageDir));
        if (!is_dir($dir)) {
            return false;
        }

        foreach((array) $rules as $part) {
            // Split patterns for single globs (should be max 260 chars)
            $patterns = explode(' ', trim($part));
            
            foreach ($patterns as $pattern) {
                try {
                    foreach (glob($dir.'/'.$pattern) as $file) {
                        $this->filesystem->remove($file);
                    }
                } catch (\Exception $e) {
                    $this->io->write("Could not parse $packageDir ($pattern): ".$e->getMessage());
                }
            }
        }

        return true;
    }
}
