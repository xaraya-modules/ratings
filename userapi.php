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

use Xaraya\Modules\UserApiClass;

/**
 * Handle the ratings user API
 *
 * @method mixed get(array $args)
 * @method mixed getitems(array $args)
 * @method mixed getmodules(array $args)
 * @method mixed getstyles(array $args)
 * @method mixed leftjoin(array $args)
 * @method mixed rate(array $args)
 * @method mixed topitems(array $args)
 * @extends UserApiClass<Module>
 */
class UserApi extends UserApiClass
{
    // ...
}
