<?php
/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$classes = 'progress-'.implode(' progress-', $displayData);
?>
<div class="ert-progress progress <?php echo $classes;?>">
    <div class="ert-progress-bar bar"></div>
    <span class="ert-progress-label">
        <?php echo JText::_('PLG_READINGTIME_PROGRESS_INDICATOR_LABEL');?>
        <span class="ert-progress-percentage">0%</span>
    </span>
</div>
