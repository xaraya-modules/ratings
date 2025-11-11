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

/**
 * ratings admin main function
 * @extends MethodClass<AdminGui>
 */
class MainMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * Ratings Module
     * @package modules
     * @subpackage ratings module
     * @category Third Party Xaraya Module
     * @version 2.0.0
     * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
     * @link http://xaraya.com/index.php/release/41.html
     * @author Jim McDonald
     * @see AdminGui::main()
     */
    public function __invoke(array $args = [])
    {
        // Security Check
        if (!$this->sec()->checkAccess('ManageRatings')) {
            return;
        }

        if (!$this->mod()->disableOverview()) {
            return [];
        } else {
            $this->ctl()->redirect($this->mod()->getURL('admin', 'view'));
        }
        // success
        return true;
    }
}
