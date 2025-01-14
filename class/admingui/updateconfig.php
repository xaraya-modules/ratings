<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\AdminGui;


use Xaraya\Modules\Ratings\AdminGui;
use Xaraya\Modules\MethodClass;
use xarVar;
use xarSec;
use xarSecurity;
use xarMod;
use xarModVars;
use xarController;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings admin updateconfig function
 * @extends MethodClass<AdminGui>
 */
class UpdateconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update configuration
     */
    public function __invoke(array $args = [])
    {
        // Get parameters
        if (!$this->fetch('ratingsstyle', 'array', $ratingsstyle, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->fetch('seclevel', 'array', $seclevel, null, xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->fetch('shownum', 'array', $shownum, null, xarVar::NOT_REQUIRED)) {
            return;
        }

        // Confirm authorisation code
        if (!$this->confirmAuthKey()) {
            return;
        }
        // Security Check
        if (!$this->checkAccess('AdminRatings')) {
            return;
        }

        $settings = ['default'];

        $hookedmodules = xarMod::apiFunc(
            'modules',
            'admin',
            'gethookedmodules',
            ['hookModName' => 'ratings']
        );

        if (isset($hookedmodules) && is_array($hookedmodules)) {
            foreach ($hookedmodules as $modname => $value) {
                // we have hooks for individual item types here
                if (!isset($value[0])) {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = xarMod::apiFunc($modname, 'user', 'getitemtypes');
                    } catch (Exception $e) {
                        $mytypes = [];
                    }
                    foreach ($value as $itemtype => $val) {
                        $settings[] = "$modname.$itemtype";
                    }
                } else {
                    $settings[] = $modname;
                }
            }
        }

        foreach ($settings as $modname) {
            if ($modname == 'default') {
                if (isset($ratingsstyle['default'])) {
                    $this->setModVar('defaultratingsstyle', $ratingsstyle['default']);
                }
                if (isset($seclevel['default'])) {
                    $this->setModVar('seclevel', $seclevel['default']);
                }
                if (!isset($shownum['default']) || $shownum['default'] != 1) {
                    $this->setModVar('shownum', 0);
                } else {
                    $this->setModVar('shownum', 1);
                }
            } else {
                if (isset($ratingsstyle[$modname])) {
                    xarModVars::set('ratings', "ratingsstyle.$modname", $ratingsstyle[$modname]);
                }
                if (isset($seclevel[$modname])) {
                    xarModVars::set('ratings', "seclevel.$modname", $seclevel[$modname]);
                }
                if (!isset($shownum[$modname]) || $shownum[$modname] != 1) {
                    xarModVars::set('ratings', "shownum.$modname", 0);
                } else {
                    xarModVars::set('ratings', "shownum.$modname", 1);
                }
            }
        }

        $this->redirect($this->getUrl('admin', 'modifyconfig'));

        return true;
    }
}
