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

/**
 * Namespace.
 */

namespace PBDKN\ExtAssets\Resources\contao\models;

/**
 * Class ExtJsModel.
 */
class ExtJsModel extends \Model
{
    protected static $strTable = 'tl_extjs';

    /**
     * Find multiple javasript groups by their IDs.
     *
     * @param array $arrIds     An array of group IDs
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|null A collection of javasript groups or null if there are no javasript groups
     */
    public static function findMultipleByIds($arrIds, array $arrOptions = [])
    {
        if (!\is_array($arrIds) || empty($arrIds)) {
            return null;
        }

        $t = static::$strTable;

        if (!isset($arrOptions['order'])) {
            $arrOptions['order'] = \Database::getInstance()->findInSet("$t.id", $arrIds);
        }

        return static::findBy(["$t.id IN(".implode(',', array_map('intval', $arrIds)).')'], null, $arrOptions);
    }
}
