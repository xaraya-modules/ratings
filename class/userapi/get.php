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
use xarMod;
use xarSecurity;
use xarDB;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings userapi get function
 */
class GetMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get a rating for a specific item
     * @param mixed $args ['modname'] name of the module this rating is for
     * @param mixed $args ['itemtype'] item type (optional)
     * @param mixed $args ['itemid'] ID of the item this rating is for
     * @return int rating the corresponding rating, or void if no rating exists
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if ((!isset($modname)) ||
            (!isset($itemid))) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                xarML('module name or item id'),
                'user',
                'get',
                'ratings'
            );
            throw new Exception($msg);
        }
        $modid = xarMod::getRegID($modname);
        if (empty($modid)) {
            $msg = xarML(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                xarML('module id'),
                'user',
                'get',
                'ratings'
            );
            throw new Exception($msg);
        }

        if (!isset($itemtype)) {
            $itemtype = 0;
        }

        // Security Check
        if (!xarSecurity::check('ReadRatings')) {
            return;
        }

        // Database information
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $ratingstable = $xartable['ratings'];
        // Get items
        $query = "SELECT rating
                FROM $ratingstable
                WHERE module_id = ?
                  AND itemid = ?
                  AND itemtype = ?";
        $bindvars = [$modid, $itemid, $itemtype];
        $result = & $dbconn->Execute($query, $bindvars);
        if (!$result || $result->EOF) {
            return;
        }
        $rating = $result->fields[0];
        $result->close();
        // Return the rating as a single number.
        // Bug 6160 requests an array with the rating and the numrating, solved by using getitems function
        return $rating;
    }
}
