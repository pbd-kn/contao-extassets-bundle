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

class ExtAssetsUpdater
{
    public static function run(): void
    {
        $objDatabase = \Database::getInstance();
        \Controller::loadDataContainer('tl_extcss');

        $arrFields = [
            'tl_extcss' => ['bootstrapVariablesSRC', 'observeFolderSRC'],
            'tl_extcss_file' => ['src'],
            'tl_extjs_file' => ['src'],
        ];

        if (version_compare(VERSION, '3.2', '>=')) {
            foreach ($arrFields as $strTable => $arrNames) {
                if (!$objDatabase->tableExists($strTable)) {
                    continue;
                }
                // convert file fields
                foreach ($objDatabase->listFields($strTable) as $arrField) {
                    // with extassets 1.1.1 bootstrapVariablesSRC changed to variablesSRC
                    if ('bootstrapVariablesSRC' === $arrField['name']) {
                        if (!$objDatabase->fieldExists('variablesSRC', $strTable)) {
                            $sql = &$GLOBALS['TL_DCA']['tl_extcss']['fields']['variablesSRC']['sql'];
                            $objDatabase->query("ALTER TABLE $strTable ADD `variablesSRC` $sql");

                            $sql = &$GLOBALS['TL_DCA']['tl_extcss']['fields']['variablesOrderSRC']['sql'];
                            $objDatabase->query("ALTER TABLE $strTable ADD `variablesOrderSRC` $sql");
                        }

                        $objGroups = $objDatabase->execute('SELECT * FROM '.$strTable.' WHERE bootstrapVariablesSRC IS NOT NULL AND variablesSRC IS NULL');

                        while ($objGroups->next()) {
                            $variables = serialize([$objGroups->bootstrapVariablesSRC]);

                            $objDatabase->prepare('UPDATE '.$strTable.' SET variablesSRC = ?, variablesOrderSRC = ? WHERE id = ?')->execute($variables, $variables, $objGroups->id);
                        }

                        $objDatabase->query("ALTER TABLE $strTable DROP `bootstrapVariablesSRC`");
                    }

                    if (\in_array($arrField['name'], $arrNames, true)) {
                        \Database\Updater::convertSingleField($strTable, $arrField['name']);
                    }
                }
            }
        }
    }
}
