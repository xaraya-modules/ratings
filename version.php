<?php

/**
 * Ratings Module
 *
 * @package modules
 * @subpackage ratings module
 * @category Third Party Xaraya Module
 * @version 2.0.0
 * @license GPL {@link http://www.gnu.org/licenses/gpl.html}
 * @link http://xaraya.com/index.php/release/41.html
 * @author Jim McDonald
 */

namespace Xaraya\Modules\Ratings;

class Version
{
    /**
     * Get module version information
     *
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'name' => 'Ratings',
            'id' => '41',
            'version' => '2.0.0',
            'displayname' => 'Ratings',
            'description' => 'Rate Xaraya items',
            'credits' => 'xardocs/credits.txt',
            'help' => 'xardocs/help.txt',
            'changelog' => 'xardocs/changelog.txt',
            'license' => 'xardocs/license.txt',
            'coding' => 'xardocs/coding.txt',
            'official' => 1,
            'author' => 'Jim McDonald',
            'contact' => 'http://www.mcdee.net/',
            'admin' => 1,
            'user' => 0,
            'class' => 'Utility',
            'category' => 'Content',
            'namespace' => 'Xaraya\\Modules\\Ratings',
            'twigtemplates' => true,
            'dependencyinfo'
             => [
                 0
                  => [
                      'name' => 'Xaraya Core',
                      'version_ge' => '2.4.1',
                  ],
             ],
        ];
    }
}
