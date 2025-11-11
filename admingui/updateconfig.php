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
use Exception;

/**
 * ratings admin updateconfig function
 * @extends MethodClass<AdminGui>
 */
class UpdateconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update configuration
     * @see AdminGui::updateconfig()
     */
    public function __invoke(array $args = [])
    {
        // Get parameters
        $this->var()->find('ratingsstyle', $ratingsstyle, 'array');
        $this->var()->find('seclevel', $seclevel, 'array');
        $this->var()->find('shownum', $shownum, 'array');

        // Confirm authorisation code
        if (!$this->sec()->confirmAuthKey()) {
            return;
        }
        // Security Check
        if (!$this->sec()->checkAccess('AdminRatings')) {
            return;
        }

        $settings = ['default'];

        $hookedmodules = $this->mod()->apiFunc(
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
                        $mytypes = $this->mod()->apiFunc($modname, 'user', 'getitemtypes');
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
                    $this->mod()->setVar('defaultratingsstyle', $ratingsstyle['default']);
                }
                if (isset($seclevel['default'])) {
                    $this->mod()->setVar('seclevel', $seclevel['default']);
                }
                if (!isset($shownum['default']) || $shownum['default'] != 1) {
                    $this->mod()->setVar('shownum', 0);
                } else {
                    $this->mod()->setVar('shownum', 1);
                }
            } else {
                if (isset($ratingsstyle[$modname])) {
                    $this->mod()->setVar("ratingsstyle.$modname", $ratingsstyle[$modname]);
                }
                if (isset($seclevel[$modname])) {
                    $this->mod()->setVar("seclevel.$modname", $seclevel[$modname]);
                }
                if (!isset($shownum[$modname]) || $shownum[$modname] != 1) {
                    $this->mod()->setVar("shownum.$modname", 0);
                } else {
                    $this->mod()->setVar("shownum.$modname", 1);
                }
            }
        }

        $this->ctl()->redirect($this->mod()->getURL('admin', 'modifyconfig'));

        return true;
    }
}
