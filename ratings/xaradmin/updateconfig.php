<?php

/**
 * Update configuration
 */
function ratings_admin_updateconfig()
{ 
    // Get parameters
    if(!xarVarFetch('style',    'isset', $style,    'outoffivestars', XARVAR_NOT_REQUIRED)) {return;}
    if(!xarVarFetch('seclevel', 'isset', $seclevel, 'medium', XARVAR_NOT_REQUIRED)) {return;}

    // Confirm authorisation code
    if (!xarSecConfirmAuthKey()) return; 
    // Security Check
    if (!xarSecurityCheck('AdminRatings')) return; 

    // Update default style
    if (!is_array($style)) {
        xarModSetVar('ratings', 'defaultstyle', $style);
    } else {
        foreach ($style as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('ratings', 'defaultstyle', $value);
            } else {
                xarModSetVar('ratings', 'style.' . $modname, $value);
            } 
        } 
    } 
    // Update security level
    if (!is_array($seclevel)) {
        xarModSetVar('ratings', 'seclevel', $seclevel);
    } else {
        foreach ($seclevel as $modname => $value) {
            if ($modname == 'default') {
                xarModSetVar('ratings', 'seclevel', $value);
            } else {
                xarModSetVar('ratings', 'seclevel.' . $modname, $value);
            } 
        } 
    } 

    xarResponseRedirect(xarModURL('ratings', 'admin', 'modifyconfig'));

    return true;
} 

?>