<?php defined('_JEXEC') or die; ?>

<?php
if ($quickTime !== $slowTime)
{
	$quickTime = $slowTime . " - " . $quickTime;
}
?>

<div class="reading-time" style="<?php echo $customStyle; ?>">
	(<?php echo JText::_('PLG_READINGTIME_LABEL'); ?>: <?php echo $quickTime . " " . JText::plural('PLG_READINGTIME_N_MINUTES', $slowTime ) ?>)
</div>

