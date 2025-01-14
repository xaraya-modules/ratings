<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
**/

namespace Xaraya\Modules\Ratings\UserApi;


use Xaraya\Modules\Ratings\UserApi;
use Xaraya\Modules\MethodClass;
use sys;
use BadParameterException;

sys::import('xaraya.modules.method');

/**
 * ratings userapi getstyles function
 * @extends MethodClass<UserApi>
 */
class GetstylesMethod extends MethodClass
{
    /** functions imported by bermuda_cleanup */

    /**
     * get the rating styles
     */
    public function __invoke(array $args = [])
    {
        $ratingstyles = [
            ['id' => 'percentage', 'name' => $this->translate('Percentage')],
            ['id' => 'outoffive', 'name' => $this->translate('Number out of five')],
            ['id' => 'outoffivestars', 'name' => $this->translate('Stars out of five')],
            ['id' => 'outoften', 'name' => $this->translate('Number out of ten')],
            ['id' => 'outoftenstars', 'name' => $this->translate('Stars out of ten')],
            ['id' => 'customised', 'name' => $this->translate('Customized: see the user-display template')],
        ];
        return $ratingstyles;
    }
}
