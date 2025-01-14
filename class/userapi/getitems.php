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
 * ratings userapi getitems function
 * @extends MethodClass<UserApi>
 */
class GetitemsMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get a rating for a list of items
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from, or
     * @var mixed $modid module id you want items from
     * @var mixed $itemtype item type (optional)
     * @var mixed $itemids array of item IDs
     * @var mixed $sort string sort by itemid (default), rating or numratings
     * @return array|void $array[$itemid] = array('numratings' => $numratings, 'rating' => $rating)
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if (!isset($modname) && !isset($modid)) {
            $msg = $this->translate(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->translate('module name'),
                'user',
                'getitems',
                'ratings'
            );
            throw new Exception($msg);
        }
        if (!empty($modname)) {
            $modid = xarMod::getRegID($modname);
        }
        if (empty($modid)) {
            $msg = $this->translate(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->translate('module id'),
                'user',
                'getitems',
                'ratings'
            );
            throw new Exception($msg);
        }
        // Bug 5856: is this needed?
        if (!isset($itemtype)) {
            $itemtype = 0;
        }
        if (empty($sort)) {
            $sort = 'itemid';
        }

        // Security Check
        if (!$this->checkAccess('ReadRatings')) {
            return;
        }

        // Database information
        $dbconn = xarDB::getConn();
        $xartable = & xarDB::getTables();
        $ratingstable = $xartable['ratings'];

        // Get items
        $query = "SELECT itemid, rating, numratings
                FROM $ratingstable
                WHERE module_id = ?
                  AND itemtype = ?";

        $bindvars[] = (int) $modid;
        $bindvars[] = (int) $itemtype;

        if (isset($itemids) && count($itemids) > 0) {
            $allids = join(', ', $itemids);
            $query .= " AND itemid IN (?)";
            $bindvars[] = $allids;
        }
        if ($sort == 'rating') {
            $query .= " ORDER BY rating DESC, numratings DESC";
        } elseif ($sort == 'numratings') {
            $query .= " ORDER BY numratings DESC, rating DESC";
        } else {
            $query .= " ORDER BY itemid ASC";
        }

        $result = & $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return;
        }

        $getitems = [];
        while (!$result->EOF) {
            [$id, $rating, $numratings] = $result->fields;
            $getitems[$id] = ['numratings' => $numratings, 'rating' => $rating];
            $result->MoveNext();
        }
        $result->close();

        return $getitems;
    }
}
