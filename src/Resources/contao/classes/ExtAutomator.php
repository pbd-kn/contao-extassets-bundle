<?php

namespace PBDKN\ExtAssets\Resources\contao\classes;

class ExtAutomator extends \Automator
{
	public function purgeLessCache()
	{
        AssetsLog::setAssetDebugmode(1);
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'purgeLessCache ');

		if(!is_array($GLOBALS['TL_PURGE']['folders']['less']['affected'])) return false;
		
		foreach($GLOBALS['TL_PURGE']['folders']['less']['affected'] as $folder)
		{
			// Purge folder
			$objFolder = new \Folder($folder);
			$objFolder->purge();
			
			// Restore the index.html file
			$objFile = new \File('templates/index.html', true);
			$objFile->copyTo($folder . 'index.html');
		}
		
		// Also empty the page cache so there are no links to deleted scripts
		$this->purgePageCache();

		// Add a log entry
		$this->log('Purged the less cache', 'ExtAssets purgeLessCache()', TL_CRON);
	}
}