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
use Xaraya\Modules\Ratings\AdminApi;
use Xaraya\Modules\MethodClass;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings admin delete function
 * @extends MethodClass<AdminGui>
 */
class DeleteMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Delete ratings of module items
     * @see AdminGui::delete()
     */
    public function __invoke(array $args = [])
    {
        /** @var AdminApi $adminapi */
        $adminapi = $this->adminapi();
        // Security Check
        if (!$this->sec()->checkAccess('DeleteRatings')) {
            return;
        }

        $this->var()->check('modid', $modid);
        $this->var()->check('itemtype', $itemtype);
        $this->var()->check('itemid', $itemid);
        $this->var()->find('confirm', $confirm, 'str:1:', '');

        // Check for confirmation.
        if (empty($confirm)) {
            $data = [];
            $data['modid'] = $modid;
            $data['itemtype'] = $itemtype;
            $data['itemid'] = $itemid;

            $what = '';
            if (!empty($modid)) {
                $modinfo = $this->mod()->getInfo($modid);
                if (empty($itemtype)) {
                    $data['modname'] = ucwords($modinfo['displayname']);
                } else {
                    // Get the list of all item types for this module (if any)
                    try {
                        $mytypes = $this->mod()->apiFunc($modinfo['name'], 'user', 'getitemtypes');
                    } catch (Exception $e) {
                        $mytypes = [];
                    }
                    if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                        $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    } else {
                        $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    }
                }
            }
            // Generate a one-time authorisation code for this operation
            $data['authid'] = $this->sec()->genAuthKey();
            // Return the template variables defined in this function
            return $data;
        }

        if (!$this->sec()->confirmAuthKey()) {
            return;
        }
        if (!$adminapi->delete(['modid' => $modid,
                'itemtype' => $itemtype,
                'itemid' => $itemid,
                'confirm' => $confirm, ]
        )) {
            return;
        }
        $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        return true;
    }
}
