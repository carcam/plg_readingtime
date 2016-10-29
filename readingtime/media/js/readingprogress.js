/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

//Adapted from https://css-tricks.com/reading-position-indicator/
jQuery(document).on('ready', function() {
    var winHeight = jQuery(window).height();
    var docHeight = getArticleHeight();
    var $progressBar = jQuery('progress');
    var max, value;

    /* Set the max scrollable area */
    max = docHeight - winHeight;
    $progressBar.attr('max', max);

    jQuery(document).on('scroll', function(){
     value = jQuery(window).scrollTop();
     $progressBar.attr('value', value);
  });
});

function getArticleHeight()
{
    var $start =jQuery('#ert-start').parent();
    if($start.length == 0)
    {
        $start = jQuery(document);
    }

    return $start.height();
}
