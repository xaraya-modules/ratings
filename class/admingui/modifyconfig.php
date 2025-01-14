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
use xarSecurity;
use xarVar;
use xarMod;
use xarModVars;
use xarController;
use xarSec;
use xarTpl;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings admin modifyconfig function
 * @extends MethodClass<AdminGui>
 */
class ModifyconfigMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Update the configuration parameters of the module based on data from the modification form
     * @author Jim McDonald
     * @access public
     * @return true|string|void on success or void on failure
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->checkAccess('AdminRatings')) {
            return;
        }

        if (!$this->fetch('phase', 'str:1:100', $phase, 'modify', xarVar::NOT_REQUIRED)) {
            return;
        }
        if (!$this->fetch('tab', 'str:1:100', $data['tab'], 'general', xarVar::NOT_REQUIRED)) {
            return;
        }

        $data['module_settings'] = xarMod::apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'ratings']);
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls');
        $data['module_settings']->getItem();

        switch (strtolower($phase)) {
            case 'modify':
            default:
                switch ($data['tab']) {
                    case 'general':

                        $defaultratingsstyle = $this->getModVar('defaultratingsstyle');
                        $defaultseclevel = $this->getModVar('seclevel');
                        $defaultshownum = $this->getModVar('shownum');

                        $data['settings'] = [];
                        $data['settings']['default'] = ['label' => $this->translate('Default configuration'),
                            'ratingsstyle' => $defaultratingsstyle,
                            'seclevel' => $defaultseclevel,
                            'shownum' => $defaultshownum, ];

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
                                        $ratingsstyle = $this->getModVar("ratingsstyle.$modname.$itemtype");
                                        if (empty($ratingsstyle)) {
                                            $ratingsstyle = $defaultratingsstyle;
                                        }
                                        $seclevel = $this->getModVar("seclevel.$modname.$itemtype");
                                        if (empty($seclevel)) {
                                            $seclevel = $defaultseclevel;
                                        }
                                        $shownum = $this->getModVar("shownum.$modname.$itemtype");
                                        if (empty($shownum)) {
                                            $shownum = $defaultshownum;
                                            xarModVars::set('ratings', "shownum.$modname.$itemtype", $defaultshownum);
                                        }
                                        if (isset($mytypes[$itemtype])) {
                                            $type = $mytypes[$itemtype]['label'];
                                            $link = $mytypes[$itemtype]['url'];
                                        } else {
                                            $type = $this->translate('type #(1)', $itemtype);
                                            $link = xarController::URL($modname, 'user', 'view', ['itemtype' => $itemtype]);
                                        }
                                        $data['settings']["$modname.$itemtype"] = ['label' => $this->translate('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                                            'ratingsstyle' => $ratingsstyle,
                                            'seclevel' => $seclevel,
                                            'shownum' => $shownum, ];
                                    }
                                } else {
                                    $ratingsstyle = $this->getModVar('ratingsstyle.' . $modname);
                                    if (empty($ratingsstyle)) {
                                        $ratingsstyle = $defaultratingsstyle;
                                    }
                                    $seclevel = $this->getModVar('seclevel.' . $modname);
                                    if (empty($seclevel)) {
                                        $seclevel = $defaultseclevel;
                                    }
                                    $shownum = $this->getModVar('shownum.' . $modname);
                                    if (empty($shownum)) {
                                        $shownum = $defaultshownum;
                                        xarModVars::set('ratings', "shownum.$modname", $defaultshownum);
                                    }
                                    $link = xarController::URL($modname, 'user', 'main');
                                    $data['settings'][$modname] = ['label' => $this->translate('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                                        'ratingsstyle' => $ratingsstyle,
                                        'seclevel' => $seclevel,
                                        'shownum' => $shownum, ];
                                }
                            }
                        }

                        $data['secleveloptions'] = [
                            ['id' => 'low', 'name' => $this->translate('Low : users can vote multiple times')],
                            ['id' => 'medium', 'name' => $this->translate('Medium : users can vote once per day')],
                            ['id' => 'high', 'name' => $this->translate('High : users must be logged in and can only vote once')],
                        ];

                        $data['authid'] = $this->genAuthKey();
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }
                break;
            case 'update':
                // Confirm authorisation code
                if (!$this->confirmAuthKey()) {
                    return xarController::badRequest('bad_author', $this->getContext());
                }
                switch ($data['tab']) {
                    case 'general':

                        $isvalid = $data['module_settings']->checkInput();
                        if (!$isvalid) {
                            $data['context'] ??= $this->getContext();
                            return xarTpl::module('eventhub', 'admin', 'modifyconfig', $data);
                        } else {
                            $itemid = $data['module_settings']->updateItem();
                        }


                        // Return
                        return true;
                        break;
                    case 'tab2':
                        break;
                    case 'tab3':
                        break;
                    default:
                        break;
                }
                break;
        }
        return $data;
    }
}
