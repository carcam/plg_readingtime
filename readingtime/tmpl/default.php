<?php
/**
 * @author    Carlos M. Cámara <carlos@hepta.es>
 * @copyright 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url       https://extensions.hepta.es
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 **/

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

if ($quickTime !== $slowTime) {
    $formattedTime = $quickTime . " - " . $slowTime;
} else {
    $formattedTime = $slowTime;
}
?>

<div class="reading-time" style="<?php echo $customStyle; ?>">
    (<?php echo Text::_('PLG_READINGTIME_LABEL'); ?>:
        <?php echo $formattedTime
        . " " .
        Text::plural('PLG_READINGTIME_N_MINUTES', $slowTime) ?>)
</div>
