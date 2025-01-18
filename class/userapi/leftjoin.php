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
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings userapi leftjoin function
 * @extends MethodClass<UserApi>
 */
class LeftjoinMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * return the field names and correct values for joining on ratings table
     * example : SELECT ..., $module_id, $itemid, $rating,...
     *           FROM ...
     *           LEFT JOIN $table
     *               ON $field = <name of itemid field>
     *           WHERE ...
     *               AND $rating > 1000
     *               AND $where
     * @param array<mixed> $args
     * @var mixed $modname name of the module you want items from, or
     * @var mixed $itemtype item type (optional) or array of itemtypes
     * @var mixed $itemids optional array of itemids that we are selecting on
     * @return array array('table' => '[SitePrefix]_ratings',
     * 'field' => '[SitePrefix]_ratings.itemid',
     * 'where' => "[SitePrefix]_ratings.itemid IN (...)
     *             AND [SitePrefix]_ratings.module_id = 123",
     * 'module_id'  => '[SitePrefix]_ratings.module_id',
     * // ...
     * 'rating'  => '[SitePrefix]_ratings.rating')
     * @return array|void
     */
    public function __invoke(array $args = [])
    {
        // Get arguments from argument array
        extract($args);

        // Optional argument
        if (!isset($modname)) {
            $modname = '';
        } else {
            $modid = xarMod::getRegID($modname);
        }
        if (!isset($modid)) {
            $modid = '';
        }
        if (!isset($itemids)) {
            $itemids = [];
        }

        // Security check
        if (count($itemids) > 0) {
            foreach ($itemids as $itemid) {
                // Security Check
                // FIXME: add some instances here
                if (!$this->sec()->checkAccess('OverviewRatings')) {
                    return;
                }
            }
        } else {
            if (!$this->sec()->checkAccess('OverviewRatings')) {
                return;
            }
        }

        // Table definition
        $xartable = & xarDB::getTables();
        $userstable = $xartable['ratings'];

        $leftjoin = [];

        // Specify LEFT JOIN ... ON ... [WHERE ...] parts
        $leftjoin['table'] = $xartable['ratings'];
        $leftjoin['field'] = '';
        if (!empty($modid)) {
            $leftjoin['field'] .= $xartable['ratings'] . ".module_id = " . $modid;
            $leftjoin['field'] .= ' AND ';
        }
        if (!empty($itemtype)) {
            if (is_numeric($itemtype)) {
                $leftjoin['field'] .= $xartable['ratings'] . '.itemtype = ' . $itemtype;
                $leftjoin['field'] .= ' AND ';
            } elseif (is_array($itemtype) && count($itemtype) > 0) {
                $seentype = [];
                foreach ($itemtype as $id) {
                    if (empty($id) || !is_numeric($id)) {
                        continue;
                    }
                    $seentype[$id] = 1;
                }
                if (count($seentype) == 1) {
                    $itemtypes = array_keys($seentype);
                    $leftjoin['field'] .= $xartable['ratings'] . '.itemtype = ' . $itemtypes[0];
                    $leftjoin['field'] .= ' AND ';
                } elseif (count($seentype) > 1) {
                    $itemtypes = join(', ', array_keys($seentype));
                    $leftjoin['field'] .= $xartable['ratings'] . '.itemtype IN (' . $itemtypes . ')';
                    $leftjoin['field'] .= ' AND ';
                }
            }
        }
        $leftjoin['field'] .= $xartable['ratings'] . '.itemid';

        if (count($itemids) > 0) {
            $allids = join(', ', $itemids);
            $leftjoin['where'] = $xartable['ratings'] . '.itemid IN (' . $allids . ')';
        } else {
            $leftjoin['where'] = '';
        }

        // Add available columns in the ratings table
        $columns = ['module_id','itemtype','itemid','rating','numratings'];
        foreach ($columns as $column) {
            $leftjoin[$column] = $xartable['ratings'] . '.' . $column;
        }
        return $leftjoin;
    }
}
