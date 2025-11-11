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
use Exception;

/**
 * ratings userapi get function
 * @extends MethodClass<UserApi>
 */
class GetMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get a rating for a specific item
     * @param array<mixed> $args
     * @var mixed $modname name of the module this rating is for
     * @var mixed $itemtype item type (optional)
     * @var mixed $itemid ID of the item this rating is for
     * @return int|void rating the corresponding rating, or void if no rating exists
     * @see UserApi::get()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if ((!isset($modname))
            || (!isset($itemid))) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->ml('module name or item id'),
                'user',
                'get',
                'ratings'
            );
            throw new Exception($msg);
        }
        $modid = $this->mod()->getRegID($modname);
        if (empty($modid)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->ml('module id'),
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
        if (!$this->sec()->checkAccess('ReadRatings')) {
            return;
        }

        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $ratingstable = $xartable['ratings'];
        // Get items
        $query = "SELECT rating
                FROM $ratingstable
                WHERE module_id = ?
                  AND itemid = ?
                  AND itemtype = ?";
        $bindvars = [$modid, $itemid, $itemtype];
        $result = & $dbconn->Execute($query, $bindvars);
        if (!$result || !$result->first()) {
            return;
        }
        $rating = $result->fields[0];
        $result->close();
        // Return the rating as a single number.
        // Bug 6160 requests an array with the rating and the numrating, solved by using getitems function
        return $rating;
    }
}
