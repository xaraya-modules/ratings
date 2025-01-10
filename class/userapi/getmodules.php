<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\UserApi;

use Xaraya\Modules\MethodClass;
use xarSecurity;
use xarDB;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings userapi getmodules function
 */
class GetmodulesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of modules for which we're rating items
     * @return array $array[$modid][$itemtype] = array('items' => $numitems,'ratings' => $numratings);
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!xarSecurity::check('OverviewRatings')) {
            return;
        }

        // Database information
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $ratingstable = $xartable['ratings'];

        // Get items
        $query = "SELECT module_id, itemtype, COUNT(itemid), SUM(numratings)
                FROM $ratingstable
                GROUP BY module_id, itemtype
                ORDER BY module_id, itemtype";

        $result = $dbconn->Execute($query);
        if (!$result) {
            return;
        }

        $modlist = [];
        while (!$result->EOF) {
            [$modid, $itemtype, $numitems, $numratings] = $result->fields;
            $modlist[$modid][$itemtype] = ['items' => $numitems, 'ratings' => $numratings];
            $result->MoveNext();
        }
        $result->close();

        return $modlist;
    }
}
