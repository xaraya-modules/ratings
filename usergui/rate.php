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
use Xaraya\Modules\Ratings\UserApi;
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
     * @return bool|void true
     * @see UserGui::rate()
     */
    public function __invoke(array $args = [])
    {
        /** @var UserApi $userapi */
        $userapi = $this->userapi();
        // Get parameters
        $this->var()->check('modname', $modname);
        $this->var()->check('itemtype', $itemtype);
        $this->var()->check('itemid', $itemid);
        $this->var()->check('returnurl', $returnurl);
        $this->var()->check('rating', $rating);

        // Confirm authorisation code
        if (!$this->sec()->confirmAuthKey()) {
            return;
        }

        // Pass to API
        $newrating = $userapi->rate(['modname'    => $modname,
                'itemtype'   => $itemtype,
                'itemid'     => $itemid,
                'rating'     => $rating, ]
        );

        if (isset($newrating)) {
            // Success
            $this->session()->setVar('ratings_statusmsg', $this->ml(
                'Thank you for rating this item.',
                'ratings'
            ));
        }

        $this->ctl()->redirect($returnurl);

        return true;
    }
}
