<?php
/**
 * @author      Carlos M. Cámara
 * @copyright   Copyright (C) 2012-2016 Hepta Technologies SL. All rights reserved.
 * @url         https://extensions.hepta.es
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use \Joomla\CMS\Language\Text;

extract($displayData);
?>
<div class="ert-progress">
<?php if ($indicatorLabel) :?>
    <label for="ert-progressbar"><?php echo Text::_('PLG_READINGTIME_PROGRESS_INDICATOR_LABEL');?> <span class="ert-progress-percentage">0%</span></label>
<?php endif; ?>
<progress id="ert-progressbar" class="ert-progress progress" max="100" value="0"></progress>
</div>

