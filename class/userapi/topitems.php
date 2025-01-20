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


use Xaraya\Modules\Ratings\UserApi;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarSecurity;
use xarDB;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings userapi topitems function
 * @extends MethodClass<UserApi>
 */
class TopitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the list of items with top N ratings for a module
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from
     * @var mixed $itemtype item type (optional)
     * @var mixed $numitems number of items to return
     * @var mixed $startnum start at this number (1-based)
     * @return array|void of array('itemid' => $itemid, 'hits' => $hits)
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($modname)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->ml('module name'),
                'user',
                'topitems',
                'ratings'
            );
            throw new Exception($msg);
        }
        $modid = xarMod::getRegID($modname);
        if (empty($modid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->ml('module id'),
                'user',
                'topitems',
                'ratings'
            );
            throw new Exception($msg);
        }

        if (!isset($itemtype)) {
            $itemtype = 0;
        }

        // Security Check
        if (!$this->sec()->checkAccess('ReadRatings')) {
            return;
        }

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $ratingstable = $xartable['ratings'];

        // Get items
        $query = "SELECT itemid, rating
                FROM $ratingstable
                WHERE module_id = ?
                  AND itemtype = ?
                ORDER BY rating DESC";
        $bindvars = [$modid, $itemtype];
        if (!isset($numitems) || !is_numeric($numitems)) {
            $numitems = 10;
        }
        if (!isset($startnum) || !is_numeric($startnum)) {
            $startnum = 1;
        }

        //$result =& $dbconn->Execute($query);
        $result = $dbconn->SelectLimit($query, $numitems, $startnum - 1, $bindvars);
        if (!$result) {
            return;
        }

        $topitems = [];
        while (!$result->EOF) {
            [$id, $rating] = $result->fields;
            $topitems[] = ['itemid' => $id, 'rating' => $rating];
            $result->MoveNext();
        }
        $result->close();
        return $topitems;
    }
}
