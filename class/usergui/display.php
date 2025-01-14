<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\UserGui;


use Xaraya\Modules\Ratings\UserGui;
use Xaraya\Modules\MethodClass;
use xarMod;
use xarModVars;
use xarVar;
use xarUser;
use xarModUserVars;
use xarSession;
use xarSecurity;
use xarSec;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings user display function
 * @extends MethodClass<UserGui>
 */
class DisplayMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * display rating for a specific item, and request rating
     * @param array<mixed> $args
     * @var mixed $itemid ID of the item this rating is for
     * @var mixed $extrainfo URL to return to if user chooses to rate
     * @var mixed $ratingsstyle ratings style to display this rating in (optional)
     * @var mixed $shownum bool to show number of ratings (optional)
     * @var mixed $showdisplay bool to show rating result (optional)
     * @var mixed $showinput bool to show rating form (optional)
     * @var mixed $itemtype item type
     * @return array|void output with rating information $numratings, $rating, $rated, $authid
     */
    public function __invoke(array $args = [])
    {
        extract($args);

        if (!empty($extrainfo['itemid'])) {
            $itemid = $extrainfo['itemid'];
        } else {
            $itemid = $objectid;
        }

        $data = [];
        $data['itemid'] = $itemid;

        $itemtype = 0;
        if (isset($extrainfo) && is_array($extrainfo)) {
            if (isset($extrainfo['module']) && is_string($extrainfo['module'])) {
                $modname = $extrainfo['module'];
            }
            if (isset($extrainfo['itemtype']) && is_numeric($extrainfo['itemtype'])) {
                $itemtype = $extrainfo['itemtype'];
            }
            if (isset($extrainfo['returnurl']) && is_string($extrainfo['returnurl'])) {
                $data['returnurl'] = $extrainfo['returnurl'];
            }
            if (isset($extrainfo['ratingsstyle']) && is_string($extrainfo['ratingsstyle'])) {
                if (in_array($ratingsstyle, ['outoffive','outoffivestars','outoften','outoftenstars','customised'])) {
                    $ratingsstyle = $extrainfo['ratingsstyle'];
                }
            }
            if (isset($extrainfo['shownum']) && ($extrainfo['shownum'] == 0 || $extrainfo['shownum'] == 1)) {
                $shownum = $extrainfo['shownum'];
            }
            if (isset($extrainfo['showdisplay']) && ($extrainfo['showdisplay'] == 0 || $extrainfo['showdisplay'] == 1)) {
                $showdisplay = $extrainfo['showdisplay'];
            }
            if (isset($extrainfo['showinput']) && ($extrainfo['showinput'] == 0 || $extrainfo['showinput'] == 0)) {
                $showinput = $extrainfo['showinput'];
            }
        } else {
            $data['returnurl'] = $extrainfo;
        }

        if (empty($modname)) {
            $modname = xarMod::getName();
        }
        //    $args['modname'] = $modname;
        //    $args['itemtype'] = $itemtype;

        if (!isset($ratingsstyle)) {
            if (!empty($itemtype)) {
                $ratingsstyle = $this->getModVar("ratingsstyle.$modname.$itemtype");
            }
            if (!isset($ratingsstyle)) {
                $ratingsstyle = $this->getModVar('ratingsstyle.' . $modname);
            }
            if (!isset($ratingsstyle)) {
                $ratingsstyle = $this->getModVar('defaultratingsstyle');
            }
        }
        if (!isset($shownum)) {
            if (!empty($itemtype)) {
                $shownum = $this->getModVar("shownum.$modname.$itemtype");
            }
            if (!isset($shownum)) {
                $shownum = $this->getModVar('shownum.' . $modname);
            }
            if (!isset($shownum)) {
                $shownum = $this->getModVar('shownum');
            }
        }

        if (isset($showdisplay) && $showdisplay != true) {
            $showdisplay = false;
        } else {
            $showdisplay = true;
        }
        if (isset($showinput) && $showinput != true) {
            $showinput = false;
        } else {
            $showinput = true;
        }

        // if we're not showing anything, bail out early
        if ($shownum == false && $showdisplay == false && $showinput == false) {
            return;
        }

        $data['ratingsstyle'] = $ratingsstyle;
        $data['modname'] = $modname;
        $data['itemtype'] = $itemtype;
        $data['shownum'] = $shownum;
        $data['showdisplay'] = $showdisplay;
        $data['showinput'] = $showinput;

        // Select the right rating
        $args['modname'] = $modname;
        $args['itemtype'] = $itemtype;
        $args['itemids'] = [$itemid];

        // Run API function
        // Bug 6160 Use getitems at first, then get if we get weird results
        $rating = xarMod::apiFunc(
            'ratings',
            'user',
            'getitems',
            $args
        );
        // Select the way to get the rating
        if (!empty($rating[$itemid])) {
            $data['rawrating'] = $rating[$itemid]['rating'];
            $data['numratings'] = $rating[$itemid]['numratings'];
        } else {
            // Use old fashioned way
            $args['itemid'] = $itemid;
            $data['rawrating'] = xarMod::apiFunc(
                'ratings',
                'user',
                'get',
                $args
            );
            $data['numratings'] = 0;
        }
        if (isset($data['rawrating'])) {
            // Set the cached variable if requested
            if (xarVar::isCached('Hooks.ratings', 'save') &&
                xarVar::getCached('Hooks.ratings', 'save') == true) {
                xarVar::setCached('Hooks.ratings', 'value', $data['rawrating']);
            }

            // Display current rating
            switch ($data['ratingsstyle']) {
                case 'percentage':
                    $data['rating'] = sprintf("%.1f", $data['rawrating']);
                    break;
                case 'outoffive':
                    $data['rating'] = round($data['rawrating'] / 20);
                    break;
                case 'outoffivestars':
                    $data['rating'] = round($data['rawrating'] / 20);
                    $data['intrating'] = (int) ($data['rawrating'] / 20);
                    $data['fracrating'] = $data['rawrating'] - (20 * $data['intrating']);
                    break;
                case 'outoften':
                    $data['rating'] = (int) ($data['rawrating'] / 10);
                    break;
                case 'outoftenstars':
                    $data['rating'] = sprintf("%.1f", $data['rawrating']);
                    $data['intrating'] = (int) ($data['rawrating'] / 10);
                    $data['fracrating'] = $data['rawrating'] - (10 * $data['intrating']);
                    break;
                case 'customised':
                default:
                    $data['rating'] = sprintf("%.1f", $data['rawrating']);
                    break;
            }
        } else {
            $data['rating'] = 0;
            $data['intrating'] = 0;
            $data['fracrating'] = 0;
        }

        // Multiple rate check
        if (!empty($itemtype)) {
            $seclevel = $this->getModVar("seclevel.$modname.$itemtype");
            if (!isset($seclevel)) {
                $seclevel = $this->getModVar('seclevel.' . $modname);
            }
        } else {
            $seclevel = $this->getModVar('seclevel.' . $modname);
        }
        if (!isset($seclevel)) {
            $seclevel = $this->getModVar('seclevel');
        }
        if ($seclevel == 'high') {
            // Check to see if user has already voted
            if (xarUser::isLoggedIn()) {
                if (!$this->getModVar($modname . ':' . $itemtype . ':' . $itemid)) {
                    xarModVars::set('ratings', $modname . ':' . $itemtype . ':' . $itemid, 1);
                }
                $rated = xarModUserVars::get('ratings', $modname . ':' . $itemtype . ':' . $itemid);
                if (!empty($rated) && $rated > 1) {
                    $data['rated'] = true;
                }
            } else {
                // no rating for anonymous users here
                $data['rated'] = true;
                // bug 5482 Always set the authid, but only a true one if security is met
                $data['authid'] = 0;
            }
        } elseif ($seclevel == 'medium') {
            // Check to see if user has already voted
            if (xarUser::isLoggedIn()) {
                if (!$this->getModVar($modname . ':' . $itemtype . ':' . $itemid)) {
                    xarModVars::set('ratings', $modname . ':' . $itemtype . ':' . $itemid, 1);
                }
                $rated = xarModUserVars::get('ratings', $modname . ':' . $itemtype . ':' . $itemid);
                if (!empty($rated) && $rated > time() - 24 * 60 * 60) {
                    $data['rated'] = true;
                }
            } else {
                $rated = xarSession::getVar('ratings:' . $modname . ':' . $itemtype . ':' . $itemid);
                if (!empty($rated) && $rated > time() - 24 * 60 * 60) {
                    $data['rated'] = true;
                }
            }
        } // No check for low

        // module name is mandatory here, because this is displayed via hooks (= from within another module)
        // set an authid, but only if the current user can rate the item
        if (xarSecurity::check('CommentRatings', 0, 'Item', "$modname:$itemtype:$itemid")) {
            $data['authid'] = xarSec::genAuthKey('ratings');
        }
        return $data;
    }
}
