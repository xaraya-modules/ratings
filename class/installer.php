<?php

/**
 * Handle module installer functions
 *
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings;

use Xaraya\Modules\InstallerClass;
use xarDB;
use xarTableDDL;
use xarModVars;
use xarModHooks;
use xarPrivileges;
use xarMasks;
use xarMod;
use sys;
use Exception;

sys::import('xaraya.modules.installer');

/**
 * Handle module installer functions
 *
 * @todo add extra use ...; statements above as needed
 * @todo replaced ratings_*() function calls with $this->*() calls
 * @extends InstallerClass<Module>
 */
class Installer extends InstallerClass
{
    /**
     * Configure this module - override this method
     *
     * @todo use this instead of init() etc. for standard installation
     * @return void
     */
    public function configure()
    {
        $this->objects = [
            // add your DD objects here
            //'ratings_object',
        ];
        $this->variables = [
            // add your module variables here
            'hello' => 'world',
        ];
        $this->oldversion = '2.4.1';
    }

    /** xarinit.php functions imported by bermuda_cleanup */

    /**
     * initialise the ratings module
     * @return bool true for the successfull install
     */
    public function init()
    {
        # --------------------------------------------------------
        #
        # Set tables
        #
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        // Load Table Maintainance API
        sys::import('xaraya.tableddl');
        // Create table
        $fields = ['id' => ['type' => 'integer', 'null' => false, 'increment' => true, 'primary_key' => true],
            'module_id' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'default' => '0'],
            'itemtype' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'default' => '0'],
            'itemid' => ['type' => 'integer', 'unsigned' => true, 'null' => false, 'default' => '0'],
            'rating' => ['type' => 'float', 'size' => 'double', 'width' => 15, 'decimals' => 5, 'null' => false, 'default' => '0'],
            'numratings' => ['type' => 'integer', 'size' => 'medium', 'null' => false, 'default' => '1'],
        ];
        // Create the Table - the function will return the SQL is successful or
        // raise an exception if it fails, in this case $query is empty
        $query = xarTableDDL::createTable($xartable['ratings'], $fields);
        if (empty($query)) {
            return;
        } // throw back

        // Pass the Table Create DDL to adodb to create the table and send exception if unsuccessful
        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }
        // TODO: compare with having 2 indexes (cfr. hitcount)
        $query = xarTableDDL::createIndex(
            $xartable['ratings'],
            ['name' => 'i_' . xarDB::getPrefix() . '_ratingcombo',
                'fields' => ['module_id', 'itemtype', 'itemid'],
                'unique' => true, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['ratings'],
            ['name' => 'i_' . xarDB::getPrefix() . '_rating',
                'fields' => ['rating'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createTable(
            $xartable['ratings_likes'],
            ['id'         => ['type'        => 'integer',
                'unsigned'    => true,
                'null'        => false,
                'increment'   => true,
                'primary_key' => true, ],
                'object_id'  => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'itemid'     => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'role_id'    => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ],
                'udid'       => ['type'        => 'integer',
                    'unsigned'    => true,
                    'null'        => false,
                    'default'     => '0', ], ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['ratings_likes'],
            ['name'   => 'i_' . xarDB::getPrefix() . '_likecombo',
                'fields' => ['object_id', 'itemid'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['ratings_likes'],
            ['name'   => 'i_' . xarDB::getPrefix() . '_role_id',
                'fields' => ['role_id'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $query = xarTableDDL::createIndex(
            $xartable['ratings_likes'],
            ['name'   => 'i_' . xarDB::getPrefix() . '_udid',
                'fields' => ['udid'],
                'unique' => false, ]
        );

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        # --------------------------------------------------------
        #
        # Set up modvars
        #
        $this->mod()->setVar('defaultratingsstyle', 'outoffivestars');
        $this->mod()->setVar('seclevel', 'medium');
        $this->mod()->setVar('shownum', 1);

        # --------------------------------------------------------
        #
        # Set up hooks
        #
        if (!xarModHooks::register(
            'item',
            'display',
            'GUI',
            'ratings',
            'user',
            'display'
        )) {
            return false;
        }

        // when a whole module is removed, e.g. via the modules admin screen
        // (set object ID to the module name !)
        if (!xarModHooks::register(
            'module',
            'remove',
            'API',
            'ratings',
            'admin',
            'deleteall'
        )) {
            return false;
        }

        /**
         * Define instances for this module
         * Format is
         * setInstance(Module,Type,ModuleTable,IDField,NameField,ApplicationVar,LevelTable,ChildIDField,ParentIDField)
         */

        $query1 = "SELECT DISTINCT $xartable[modules].name FROM $xartable[ratings] LEFT JOIN $xartable[modules] ON $xartable[ratings].module_id = $xartable[modules].regid";
        $query2 = "SELECT DISTINCT itemtype FROM $xartable[ratings]";
        $query3 = "SELECT DISTINCT itemid FROM $xartable[ratings]";
        $instances = [
            ['header' => 'Module Name:',
                'query' => $query1,
                'limit' => 20,
            ],
            ['header' => 'Item Type:',
                'query' => $query2,
                'limit' => 20,
            ],
            ['header' => 'Item ID:',
                'query' => $query3,
                'limit' => 20,
            ],
        ];
        xarPrivileges::defineInstance('ratings', 'Item', $instances);
        $ratingstable = $xartable['ratings'];

        $query1 = "SELECT DISTINCT $xartable[modules].name FROM $xartable[ratings] LEFT JOIN $xartable[modules] ON $xartable[ratings].module_id = $xartable[modules].regid";
        $query2 = "SELECT DISTINCT itemtype FROM $xartable[ratings]";
        $query3 = "SELECT DISTINCT itemid FROM $xartable[ratings]";
        $instances = [
            ['header' => 'Module Name:',
                'query' => $query1,
                'limit' => 20,
            ],
            ['header' => 'Item Type:',
                'query' => $query2,
                'limit' => 20,
            ],
            ['header' => 'Item ID:',
                'query' => $query3,
                'limit' => 20,
            ],
        ];
        // FIXME: this seems to be some left-over from the old template module
        xarPrivileges::defineInstance('ratings', 'Template', $instances);

        /**
         * Register the module components that are privileges objects
         * Format is
         * xarMasks::register(Name,Realm,Module,Component,Instance,Level,Description)
         */

        xarMasks::register('OverviewRatings', 'All', 'ratings', 'All', 'All', 'ACCESS_OVERVIEW');
        xarMasks::register('ReadRatings', 'All', 'ratings', 'All', 'All', 'ACCESS_READ');
        xarMasks::register('AddRatings', 'All', 'ratings', 'All', 'All', 'ACCESS_ADD');
        xarMasks::register('ManageRatings', 'All', 'ratings', 'All', 'All', 'ACCESS_DELETE');
        xarMasks::register('AdminRatings', 'All', 'ratings', 'All', 'All', 'ACCESS_ADMIN');

        xarMasks::register('CommentRatings', 'All', 'ratings', 'Item', 'All:All:All', 'ACCESS_COMMENT');
        xarMasks::register('EditRatingsTemplate', 'All', 'ratings', 'Template', 'All:All:All', 'ACCESS_ADMIN');
        // Initialisation successful
        return true;
    }

    /**
     * upgrade the ratings module from an old version
     * @param string oldversion
     * @return bool true on success of upgrade
     */
    public function upgrade($oldversion)
    {
        // Upgrade dependent on old version number
        switch ($oldversion) {
            case '1.0':
                // Code to upgrade from version 1.0 goes here
            case '1.1':
                // Code to upgrade from version 1.1 goes here
                // delete/initialize the whole thing again
                $this->delete();
                $this->init();
                // no break
            case '1.2.0':
                // clean up double hook registrations
                xarModHooks::unregister('module', 'remove', 'API', 'ratings', 'admin', 'deleteall');
                xarModHooks::register('module', 'remove', 'API', 'ratings', 'admin', 'deleteall');
                $hookedmodules = xarMod::apiFunc(
                    'modules',
                    'admin',
                    'gethookedmodules',
                    ['hookModName' => 'ratings']
                );
                if (isset($hookedmodules) && is_array($hookedmodules)) {
                    foreach ($hookedmodules as $modname => $value) {
                        foreach ($value as $itemtype => $val) {
                            xarMod::apiFunc(
                                'modules',
                                'admin',
                                'enablehooks',
                                ['hookModName' => 'ratings',
                                    'callerModName' => $modname,
                                    'callerItemType' => $itemtype, ]
                            );
                        }
                    }
                }

                // no break
            case '1.2.1':
                // Set up shownum modvar, including for existing hooked modules
                $this->mod()->setVar('shownum', 1);
                $hookedmodules = xarMod::apiFunc(
                    'modules',
                    'admin',
                    'gethookedmodules',
                    ['hookModName' => 'ratings']
                );
                if (isset($hookedmodules) && is_array($hookedmodules)) {
                    foreach ($hookedmodules as $modname => $value) {
                        // we have hooks for individual item types here
                        if (!isset($value[0])) {
                            // Get the list of all item types for this module (if any)
                            try {
                                $mytypes = xarMod::apiFunc($modname, 'user', 'getitemtypes');
                            } catch (Exception $e) {
                                $mytypes = [];
                            }
                            foreach ($value as $itemtype => $val) {
                                $this->mod()->setVar("shownum.$modname.$itemtype", 1);
                            }
                        } else {
                            $this->mod()->setVar('shownum.' . $modname, 1);
                        }
                    }
                }

                // modify field xar_ratings.rating
                // Get database information
                $dbconn = xarDB::getConn();
                $xartable = & xarDB::getTables();
                $query = "ALTER TABLE " . $xartable['ratings'] . "
                               MODIFY COLUMN rating double(8,5) NOT NULL default '0.00000'";
                $result = & $dbconn->Execute($query);
                if (!$result) {
                    return;
                }
        }
        return true;
    }

    /**
     * delete the ratings module
     * @return bool true on successfull deletion
     */
    public function delete()
    {
        // Remove module hooks
        if (!xarModHooks::unregister(
            'item',
            'display',
            'GUI',
            'ratings',
            'user',
            'display'
        )) {
            return;
        }

        if (!xarModHooks::unregister(
            'module',
            'remove',
            'API',
            'ratings',
            'admin',
            'deleteall'
        )) {
            return;
        }
        return xarMod::apiFunc('modules', 'admin', 'standarddeinstall', ['module' => 'ratings']);
    }
}
