<?php defined('_JEXEC') or die; ?>

<?php
if ($quickTime !== $slowTime)
{
	$formattedTime = $quickTime . " - " . $slowTime;
}
else
{
	$formattedTime = $slowTime;
}
?>

<div class="reading-time" style="<?php echo $customStyle; ?>">
	(<?php echo JText::_('PLG_READINGTIME_LABEL'); ?>: <?php echo $formattedTime . " " . JText::plural('PLG_READINGTIME_N_MINUTES', $slowTime ) ?>)
</div>

