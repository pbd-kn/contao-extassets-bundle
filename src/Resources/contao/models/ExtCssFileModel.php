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
use Contao\Model;

if (!class_exists('ExtCssFileModel')) {
    /**
     * Class ExtCssFileModel.
     */
    class ExtCssFileModel extends Model
    {
        protected static $strTable = 'tl_extcss_file';

        /**
         * Find multiple css files by their pid.
         *
         * @param array $arrOptions An optional options array
         *
         * @return \Model\Collection|null A collection of css files or null if there are no css files
         */
        public static function findMultipleByPids(array $arrPids = [], array $arrOptions = [])
        {
            $t = static::$strTable;

            if (!\is_array($arrPids) || empty($arrPids)) {
                return null;
            }

            $arrColumns = ["$t.pid IN(".implode(',', array_map('intval', $arrPids)).')'];

            if (!isset($arrOptions['order'])) {
                $arrOptions['order'] = "$t.sorting";
            }

            return static::findBy($arrColumns, null, $arrOptions);
        }
    }
}
