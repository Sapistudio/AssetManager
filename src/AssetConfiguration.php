<?php
namespace Sapistudio\AssetManager;

use Composer\Package\PackageInterface;

class AssetConfiguration
{
    protected $types    = [];
    protected $packages = [];

    public function __construct($extra = [])
    {
        if (isset($extra['custom-installer'])){
            $specsSearch = (is_array($extra['custom-installer'])) ? $extra['custom-installer'] : [$extra['custom-installer']];
            foreach ($specsSearch as $specIndex => $specName) {
                $match = [];
                if (preg_match('/^type:(.*)$/', $specName, $match)) {
                    $this->types[$match[1]] = '{$vendor}/{$name}/';
                }
                else {
                    $this->packages[$specName] = '{$vendor}/{$name}/';;
                }
            }
        }
    }

    /** Retrieve the pattern for the given package.*/
    public function getPattern(PackageInterface $package)
    {
        if(isset($this->packages[$package->getName()])) {
            return $this->packages[$package->getName()];
        } elseif (isset($this->packages[$package->getPrettyName()])) {
            return $this->packages[$package->getPrettyName()];
        } elseif(isset($this->types[$package->getType()])) {
            return $this->types[$package->getType()];
        }
    }

    /** Checks if the given configuration will handle the package type.*/
    public function isPackageTypeSupported($packageType)
    {
        if (in_array($packageType, ['metapackage', 'composer-plugin']))
            return false;
        return isset($this->types[$packageType]);
    }
}
