<?php

declare(strict_types=1);

/*
 *
 *  Contao Open Source CMS
 *
 *  Copyright (c) 2005-2014 Leo Feyer
 *
 *
 *  Contao Open Source CMS
 *
 *  Copyright (C) 2005-2013 Leo Feyer
 *   @package   Extassets
 *   @author    r.kaltofen@heimrich-hannot.de
 *   @license   GNU/LGPL
 *   @copyright Heimrich & Hannot GmbH
 *
 *  The namespaces for psr-4 were revised.
 *
 *  @package   contao-extasset-bundle
 *  @author    Peter Broghammer <pb-contao@gmx.de>
 *  @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 *  @copyright Peter Broghammer 2021-
 *
 *  Bootstrap's selection introduced.
 *
 */

namespace PBDKN\ExtAssets\Resources\contao\classes;

class ExtAutomator extends \Automator
{
    public function purgeLessCache()
    {
        AssetsLog::setAssetDebugmode(1);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'purgeLessCache ');

        if (!\is_array($GLOBALS['TL_PURGE']['folders']['less']['affected'])) {
            return false;
        }

        foreach ($GLOBALS['TL_PURGE']['folders']['less']['affected'] as $folder) {
            // Purge folder
            $objFolder = new \Folder($folder);
            $objFolder->purge();

            // Restore the index.html file
            $objFile = new \File('templates/index.html', true);
            $objFile->copyTo($folder.'index.html');
        }

        // Also empty the page cache so there are no links to deleted scripts
        $this->purgePageCache();

        // Add a log entry
        $this->log('Purged the less cache', 'ExtAssets purgeLessCache()', TL_CRON);
    }
}
