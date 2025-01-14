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
use xarVar;
use xarSec;
use xarMod;
use xarSession;
use xarController;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings user rate function
 * @extends MethodClass<UserGui>
 */
class RateMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     *
     * @return bool true
     */
    public function __invoke(array $args = [])
    {
        // Get parameters
        if (!$this->fetch('modname', 'isset', $modname, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('itemtype', 'isset', $itemtype, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('itemid', 'isset', $itemid, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('returnurl', 'isset', $returnurl, null, xarVar::DONT_SET)) {
            return;
        }
        if (!$this->fetch('rating', 'isset', $rating, null, xarVar::DONT_SET)) {
            return;
        }

        // Confirm authorisation code
        if (!$this->confirmAuthKey()) {
            return;
        }

        // Pass to API
        $newrating = xarMod::apiFunc(
            'ratings',
            'user',
            'rate',
            ['modname'    => $modname,
                'itemtype'   => $itemtype,
                'itemid'     => $itemid,
                'rating'     => $rating, ]
        );

        if (isset($newrating)) {
            // Success
            xarSession::setVar('ratings_statusmsg', $this->translate(
                'Thank you for rating this item.',
                'ratings'
            ));
        }

        $this->redirect($returnurl);

        return true;
    }
}
