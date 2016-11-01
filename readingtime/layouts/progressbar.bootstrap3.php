<?php
/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

$classes = array();
if($indicatorBarContext){
    $classes[] = 'progress-bar-' . $indicatorBarContext;
}
if($indicatorBarAnimated){
    $classes[] = 'active';
}
if($indicatorBarStriped){
    $classes[] = 'progress-bar-' . $indicatorBarStriped;
}

?>
<div class="ert-progress progress">
    <div class="ert-progress-bar progress-bar <?php echo implode (' ', $classes);?>" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
     <?php if ($indicatorLabel) :?>
        <span class="ert-progress-label">
            <?php echo JText::_('PLG_READINGTIME_PROGRESS_INDICATOR_LABEL');?>
            <span class="ert-progress-percentage">0%</span>
        </span>
    <?php endif; ?>
</div>
