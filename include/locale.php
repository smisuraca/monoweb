<?
function ftime($format, $time) {
    $REPLACE = array(
    'January','February','March','April','May','June','July','August','September','October','November','December',
    'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec',
    'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday',
    'Mon','Tue','Wed','Thu','Fri','Sat','Sun'
    );

    $aux = strftime($format, $time);
    return str_replace($REPLACE, $GLOBALS["LOCALE"], $aux);
}
?>