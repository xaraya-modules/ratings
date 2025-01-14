<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\AdminApi;


use Xaraya\Modules\Ratings\AdminApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarDB;
use xarModHooks;
use xarModVars;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings adminapi deleteall function
 * @extends MethodClass<AdminApi>
 */
class DeleteallMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * delete all ratings items for a module - hook for ('module','remove','API')
     * @param array<mixed> $args
     * @var mixed $itemid ID of the itemid (must be the module name here !!)
     * @var mixed $extrainfo extra information
     * @return bool|void true on success, false on failure
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        // When called via hooks, we should get the real module name from itemid
        // here, because the current module is probably going to be 'modules' !!!
        if (!isset($itemid) || !is_string($itemid)) {
            $msg = $this->translate(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'itemid (= module name)',
                'admin',
                'deleteall',
                'ratings'
            );
            throw new Exception($msg);
        }

        $modid = xarMod::getRegID($objectid);
        if (empty($modid)) {
            $msg = $this->translate(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                'module ID',
                'admin',
                'deleteall',
                'ratings'
            );
            throw new Exception($msg);
        }

        // TODO: re-evaluate this for hook calls !!
        // Security check - important to do this as early on as possible to
        // avoid potential security holes or just too much wasted processing
        if (!$this->checkAccess('DeleteRatings')) {
            return;
        }

        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $ratingstable = $xartable['ratings'];

        $query = "DELETE FROM $ratingstable
                WHERE module_id = ?";
        $result = & $dbconn->Execute($query, [$modid]);
        if (!$result) {
            return;
        }

        // hmmm, I think we'll skip calling more hooks here... :-)
        //xarModHooks::call('item', 'delete', '', '');

        // TODO: delete user votes with xarModVars::delete('ratings',"$modname:$itemtype:$itemid");

        // Return the extra info
        if (!isset($extrainfo)) {
            $extrainfo = [];
        }
        return $extrainfo;
    }
}
