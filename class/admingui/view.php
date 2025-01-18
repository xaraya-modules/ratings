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
use xarController;
use sys;
use Exception;

sys::import('xaraya.modules.method');

/**
 * ratings admin view function
 * @extends MethodClass<AdminGui>
 */
class ViewMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * View statistics about ratings
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('AdminRatings')) {
            return;
        }

        if (!$this->var()->check('modid', $modid)) {
            return;
        }
        if (!$this->var()->check('itemtype', $itemtype)) {
            return;
        }
        if (!$this->var()->check('itemid', $itemid)) {
            return;
        }
        if (!$this->var()->check('sort', $sort)) {
            return;
        }

        $data = [];

        if (empty($modid)) {
            $modlist = xarMod::apiFunc('ratings', 'user', 'getmodules');

            $data['moditems'] = [];
            $data['numitems'] = 0;
            $data['numratings'] = 0;
            foreach ($modlist as $modid => $itemtypes) {
                $modinfo = xarMod::getInfo($modid);
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                foreach ($itemtypes as $itemtype => $stats) {
                    $moditem = [];
                    $moditem['numitems'] = $stats['items'];
                    $moditem['numratings'] = $stats['ratings'];
                    if ($itemtype == 0) {
                        $moditem['name'] = ucwords($modinfo['displayname']);
                        //    $moditem['link'] = xarController::URL($modinfo['name'],'user','main');
                    } else {
                        if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                            $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                            //    $moditem['link'] = $mytypes[$itemtype]['url'];
                        } else {
                            $moditem['name'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                            //    $moditem['link'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                        }
                    }
                    $moditem['link'] = $this->mod()->getURL(
                        'admin',
                        'view',
                        ['modid' => $modid,
                            'itemtype' => empty($itemtype) ? null : $itemtype, ]
                    );
                    $moditem['delete'] = $this->mod()->getURL(
                        'admin',
                        'delete',
                        ['modid' => $modid,
                            'itemtype' => empty($itemtype) ? null : $itemtype, ]
                    );
                    $data['moditems'][] = $moditem;
                    $data['numitems'] += $moditem['numitems'];
                    $data['numratings'] += $moditem['numratings'];
                }
            }
            $data['delete'] = $this->mod()->getURL('admin', 'delete');
        } else {
            $modinfo = xarMod::getInfo($modid);
            if (empty($itemtype)) {
                $data['modname'] = ucwords($modinfo['displayname']);
                $itemtype = null;
            } else {
                // Get the list of all item types for this module (if any)
                try {
                    $mytypes = xarMod::apiFunc($modinfo['name'], 'user', 'getitemtypes');
                } catch (Exception $e) {
                    $mytypes = [];
                }
                if (isset($mytypes) && !empty($mytypes[$itemtype])) {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype . ' - ' . $mytypes[$itemtype]['label'];
                    //    $data['modlink'] = $mytypes[$itemtype]['url'];
                } else {
                    $data['modname'] = ucwords($modinfo['displayname']) . ' ' . $itemtype;
                    //    $data['modlink'] = xarController::URL($modinfo['name'],'user','view',array('itemtype' => $itemtype));
                }
            }

            $data['modid'] = $modid;
            $data['moditems'] = xarMod::apiFunc(
                'ratings',
                'user',
                'getitems',
                ['modid' => $modid,
                    'itemtype' => $itemtype,
                    'sort' => $sort, ]
            );
            $data['numratings'] = 0;
            foreach ($data['moditems'] as $itemid => $moditem) {
                $data['numratings'] += $moditem['numratings'];
                $data['moditems'][$itemid]['delete'] = $this->mod()->getURL(
                    'admin',
                    'delete',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'itemid' => $itemid, ]
                );
            }
            $data['delete'] = $this->mod()->getURL(
                'admin',
                'delete',
                ['modid' => $modid,
                    'itemtype' => $itemtype, ]
            );
            $data['sortlink'] = [];
            if (empty($sort) || $sort == 'itemid') {
                $data['sortlink']['itemid'] = '';
            } else {
                $data['sortlink']['itemid'] = $this->mod()->getURL(
                    'admin',
                    'view',
                    ['modid' => $modid,
                        'itemtype' => $itemtype, ]
                );
            }
            if (!empty($sort) && $sort == 'numratings') {
                $data['sortlink']['numratings'] = '';
            } else {
                $data['sortlink']['numratings'] = $this->mod()->getURL(
                    'admin',
                    'view',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'sort' => 'numratings', ]
                );
            }
            if (!empty($sort) && $sort == 'rating') {
                $data['sortlink']['rating'] = '';
            } else {
                $data['sortlink']['rating'] = $this->mod()->getURL(
                    'admin',
                    'view',
                    ['modid' => $modid,
                        'itemtype' => $itemtype,
                        'sort' => 'rating', ]
                );
            }
        }

        return $data;
    }
}
