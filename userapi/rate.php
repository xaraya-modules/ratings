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
use xarModVars;
use xarUser;
use xarModUserVars;
use xarSession;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings userapi rate function
 * @extends MethodClass<UserApi>
 */
class RateMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * rate an item
     * @param array<mixed> $args
     * @var mixed $modname module name of the item to rate
     * @var mixed $itemtype item type (optional)
     * @var mixed $itemid ID of the item to rate
     * @var mixed $rating actual rating
     * @return int|void the new rating for this item
     * @see UserApi::rate()
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Argument check
        if ((!isset($modname)) ||
            (!isset($itemid)) ||
            (!isset($rating) || !is_numeric($rating) || $rating < 0 || $rating > 100)) {
            $msg = $this->ml(
                'Invalid #(1) for #(2) function #(3)() in module #(4)',
                $this->ml('value'),
                'user',
                'rate',
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
                'rate',
                'ratings'
            );
            throw new Exception($msg);
        }

        if (!isset($itemtype)) {
            $itemtype = 0;
        }

        // Security Check
        if (!xarSecurity::check('CommentRatings', 1, 'Item', "$modname:$itemtype:$itemid")) {
            return;
        }


        // Database information
        $dbconn = $this->db()->getConn();
        $xartable = & $this->db()->getTables();
        $ratingstable = $xartable['ratings'];

        // Multipe rate check
        if (!empty($itemtype)) {
            $seclevel = $this->mod()->getVar("seclevel.$modname.$itemtype");
            if (!isset($seclevel)) {
                $seclevel = $this->mod()->getVar('seclevel.' . $modname);
            }
        } else {
            $seclevel = $this->mod()->getVar('seclevel.' . $modname);
        }
        if (!isset($seclevel)) {
            $seclevel = $this->mod()->getVar('seclevel');
        }
        if ($seclevel == 'high') {
            if (xarUser::isLoggedIn()) {
                $rated = xarModUserVars::get('ratings', $modname . ':' . $itemtype . ':' . $itemid);
                if (!empty($rated) && $rated > 1) {
                    return;
                }
            } else {
                return;
            }
        } elseif ($seclevel == 'medium') {
            // Check to see if user has already voted
            if (xarUser::isLoggedIn()) {
                $rated = xarModUserVars::get('ratings', $modname . ':' . $itemtype . ':' . $itemid);
                if (!empty($rated) && $rated > time() - 24 * 60 * 60) {
                    return;
                }
            } else {
                $rated = $this->session()->getVar('ratings:' . $modname . ':' . $itemtype . ':' . $itemid);
                if (!empty($rated) && $rated > time() - 24 * 60 * 60) {
                    return;
                }
            }
        } // No check for low

        // Get current information on rating
        $query = "SELECT id,
                       rating,
                       numratings
                FROM $ratingstable
                WHERE module_id = ?
                  AND itemid = ?
                  AND itemtype = ?";
        $bindvars = [$modid, $itemid, $itemtype];
        $result = & $dbconn->Execute($query, $bindvars);
        if (!$result) {
            return;
        }

        if (!$result->EOF) {
            // Update current rating
            [$id, $currating, $numratings] = $result->fields;
            $result->close();

            // Calculate new rating
            $newnumratings = $numratings + 1;
            $newrating = (($currating * $numratings) + $rating) / $newnumratings;

            // Insert new rating
            $query = "UPDATE $ratingstable
                    SET rating = ?,
                        numratings = ?
                    WHERE id = ?";
            $bindvars = [$newrating, $newnumratings, $id];
            $result = & $dbconn->Execute($query, $bindvars);
            if (!$result) {
                return;
            }
        } else {
            $result->close();

            // Get a new ratings ID
            $id = $dbconn->GenId($ratingstable);
            // Create new rating
            $query = "INSERT INTO $ratingstable(id,
                                              module_id,
                                              itemid,
                                              itemtype,
                                              rating,
                                              numratings)
                    VALUES (?,
                            ?,
                            ?,
                            ?,
                            ?,
                            ?)";
            $bindvars = [$id, $modid, $itemid, $itemtype, $rating, 1];
            $result = & $dbconn->Execute($query, $bindvars);
            if (!$result) {
                return;
            }

            $newrating = $rating;
        }

        // Set note that user has rated this item if required
        if ($seclevel == 'high') {
            if (xarUser::isLoggedIn()) {
                xarModUserVars::set('ratings', $modname . ':' . $itemtype . ':' . $itemid, time());
            } else {
                // nope
            }
        } elseif ($seclevel == 'medium') {
            if (xarUser::isLoggedIn()) {
                xarModUserVars::set('ratings', $modname . ':' . $itemtype . ':' . $itemid, time());
            } else {
                $this->session()->setVar('ratings:' . $modname . ':' . $itemtype . ':' . $itemid, time());
            }
        }
        // CHECKME: find some cleaner way to update the page cache if necessary
        if (function_exists('xarOutputFlushCached') &&
            xarModVars::get('cachemanager', 'FlushOnNewRating')) {
            $modinfo = $this->mod()->getInfo($modid);
            // this may not be agressive enough flushing for all sites
            // we could flush "$modinfo[name]-" to remove all output cache associated with a module
            xarOutputFlushCached("$modinfo[name]-user-display-");
        }
        return $newrating;
    }
}
