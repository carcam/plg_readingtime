<?php
/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if ($quickTime !== $slowTime)
{
    $formattedTime = $quickTime . " - " . $slowTime;
}
else
{
    $formattedTime = $slowTime;
}
?>

<div class="reading-time">
    (<?php echo JText::_('PLG_READINGTIME_LABEL'); ?>: <?php echo $formattedTime . " " . JText::plural('PLG_READINGTIME_N_MINUTES', $slowTime ) ?>)
</div>
