<?php

declare(strict_types=1);


namespace PBDKN\ExtAssets\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use PBDKN\Extassets\ContaoExtassets;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoExtassets::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
                        ];
    }
}
