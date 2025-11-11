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
     * @see AdminGui::modifyconfig()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminRatings')) {
            return;
        }

        $this->var()->find('phase', $phase, 'str:1:100', 'modify');
        $this->var()->find('tab', $data['tab'], 'str:1:100', 'general');

        $data['module_settings'] = $this->mod()->apiFunc('base', 'admin', 'getmodulesettings', ['module' => 'ratings']);
        $data['module_settings']->setFieldList('items_per_page, use_module_alias, module_alias_name, enable_short_urls');
        $data['module_settings']->getItem();

        switch (strtolower($phase)) {
            case 'modify':
            default:
                switch ($data['tab']) {
                    case 'general':

                        $defaultratingsstyle = $this->mod()->getVar('defaultratingsstyle');
                        $defaultseclevel = $this->mod()->getVar('seclevel');
                        $defaultshownum = $this->mod()->getVar('shownum');

                        $data['settings'] = [];
                        $data['settings']['default'] = ['label' => $this->ml('Default configuration'),
                            'ratingsstyle' => $defaultratingsstyle,
                            'seclevel' => $defaultseclevel,
                            'shownum' => $defaultshownum, ];

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
                                        $ratingsstyle = $this->mod()->getVar("ratingsstyle.$modname.$itemtype");
                                        if (empty($ratingsstyle)) {
                                            $ratingsstyle = $defaultratingsstyle;
                                        }
                                        $seclevel = $this->mod()->getVar("seclevel.$modname.$itemtype");
                                        if (empty($seclevel)) {
                                            $seclevel = $defaultseclevel;
                                        }
                                        $shownum = $this->mod()->getVar("shownum.$modname.$itemtype");
                                        if (empty($shownum)) {
                                            $shownum = $defaultshownum;
                                            $this->mod()->setVar("shownum.$modname.$itemtype", $defaultshownum);
                                        }
                                        if (isset($mytypes[$itemtype])) {
                                            $type = $mytypes[$itemtype]['label'];
                                            $link = $mytypes[$itemtype]['url'];
                                        } else {
                                            $type = $this->ml('type #(1)', $itemtype);
                                            $link = $this->ctl()->getModuleURL($modname, 'user', 'view', ['itemtype' => $itemtype]);
                                        }
                                        $data['settings']["$modname.$itemtype"] = ['label' => $this->ml('Configuration for #(1) module - <a href="#(2)">#(3)</a>', $modname, $link, $type),
                                            'ratingsstyle' => $ratingsstyle,
                                            'seclevel' => $seclevel,
                                            'shownum' => $shownum, ];
                                    }
                                } else {
                                    $ratingsstyle = $this->mod()->getVar('ratingsstyle.' . $modname);
                                    if (empty($ratingsstyle)) {
                                        $ratingsstyle = $defaultratingsstyle;
                                    }
                                    $seclevel = $this->mod()->getVar('seclevel.' . $modname);
                                    if (empty($seclevel)) {
                                        $seclevel = $defaultseclevel;
                                    }
                                    $shownum = $this->mod()->getVar('shownum.' . $modname);
                                    if (empty($shownum)) {
                                        $shownum = $defaultshownum;
                                        $this->mod()->setVar("shownum.$modname", $defaultshownum);
                                    }
                                    $link = $this->ctl()->getModuleURL($modname, 'user', 'main');
                                    $data['settings'][$modname] = ['label' => $this->ml('Configuration for <a href="#(1)">#(2)</a> module', $link, $modname),
                                        'ratingsstyle' => $ratingsstyle,
                                        'seclevel' => $seclevel,
                                        'shownum' => $shownum, ];
                                }
                            }
                        }

                        $data['secleveloptions'] = [
                            ['id' => 'low', 'name' => $this->ml('Low : users can vote multiple times')],
                            ['id' => 'medium', 'name' => $this->ml('Medium : users can vote once per day')],
                            ['id' => 'high', 'name' => $this->ml('High : users must be logged in and can only vote once')],
                        ];

                        $data['authid'] = $this->sec()->genAuthKey();
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
                if (!$this->sec()->confirmAuthKey()) {
                    return $this->ctl()->badRequest('bad_author');
                }
                switch ($data['tab']) {
                    case 'general':

                        $isvalid = $data['module_settings']->checkInput();
                        if (!$isvalid) {
                            $data['context'] ??= $this->getContext();
                            return $this->tpl()->module('eventhub', 'admin', 'modifyconfig', $data);
                        } else {
                            $itemid = $data['module_settings']->updateItem();
                        }


                        // Return
                        return true;
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
