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
    var $progressBar = jQuery('progress.ert-progress');
    var max, value, percentage;

    /* Set the max scrollable area */
    max = docHeight - winHeight;

    if ($progressBar.length>0){
        $progressBar.attr('max', max);
        jQuery(document).on('scroll', function(){
            value = jQuery(window).scrollTop();
            $progressBar.attr('value', value);
        });
    } else {
        $progressBar = jQuery('div.ert-progress-bar');

        jQuery(document).on('scroll', function(){
            value = jQuery(window).scrollTop();
            percentage = Math.round((value/max) * 100);
            if(percentage >= 100) {
                percentage = 100;
            }
            $progressBar.width(percentage+'%');
            $progressLabel = jQuery('.ert-progress-percentage');
            $progressLabel.html(percentage+'%');
        });
    }
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
