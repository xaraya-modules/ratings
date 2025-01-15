<?php

/**
 * @package modules\ratings
 * @category Xaraya Web Applications Framework
 * @version 2.5.7
 * @copyright see the html/credits.html file in this release
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link https://github.com/mikespub/xaraya-modules
 **/

namespace Xaraya\Modules\Ratings;

use Xaraya\Modules\UserGuiClass;
use sys;

sys::import('xaraya.modules.usergui');
sys::import('modules.ratings.class.userapi');

/**
 * Handle the ratings user GUI
 *
 * @method mixed display(array $args)
 * @method mixed main(array $args)
 * @method mixed rate(array $args)
 * @extends UserGuiClass<Module>
 */
class UserGui extends UserGuiClass
{
    // ...
}
