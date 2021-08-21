<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * @package   ExtAssets
 * @author    r.kaltofen@heimrich-hannot.de
 * @license   GNU/LGPL
 * @copyright Heimrich & Hannot GmbH
 */


/**
 * Namespace
 */
namespace PBDKN\ExtAssets\Resources\contao\models;
use PBDKN\ExtAssets\Resources\contao\classes\AssetsLog;

/**
 * Class ExtcssModel
 */
class ExtCssModel extends \Model
{

	protected static $strTable = 'tl_extcss';

	/**
	 * Find multiple css groups by their IDs
	 *
	 * @param array $arrIds     An array of group IDs
	 * @param array $arrOptions An optional options array
	 *
	 * @return \Model\Collection|null A collection of css groups or null if there are no css groups
	 */
	public static function findMultipleByIds($arrIds, array $arrOptions=array())
	{
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, '-> ');

		if (!is_array($arrIds) || empty($arrIds))
		{
            AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'arrIds leer ');
			return null;
		}

		$t = static::$strTable;
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'table '.$t);

		if (!isset($arrOptions['order']))
		{
			$arrOptions['order'] = \Database::getInstance()->findInSet("$t.id", $arrIds);
		}

		return static::findBy(array("$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")"), null, $arrOptions);
	}

	public static function findMultipleBootstrapByIds($arrIds, array $arrOptions=array())
	{
        AssetsLog::setAssetDebugmode(1);
		if (!is_array($arrIds) || empty($arrIds))
		{
			return null;
		}

		$t = static::$strTable;
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'findMultipleBootstrapByIds table '.$t);

		if (!isset($arrOptions['order']))
		{
        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'order not set');
			$arrOptions['order'] = \Database::getInstance()->findInSet("$t.id", $arrIds);
		}

        AssetsLog::ExtAssetWriteLog(1, __METHOD__, __LINE__, 'vor return array ' . "$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")");
		return static::findBy(array("$t.id IN(" . implode(',', array_map('intval', $arrIds)) . ")"), null, $arrOptions);
	}
}