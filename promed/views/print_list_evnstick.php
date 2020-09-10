<?php 
$rows = array_map(function($item) {
	$text = "Случай временной нетрудоспособности № {$item['EvnStick_Num']}, {$item['WorkReleaseRange']}, {$item['TotalDaysCount']} дней";
	if (!empty($item['RelatedPerson'])) $text .= ", {$item['RelatedPerson']}";
	return "<tr><td style=\"font-size: 10px;\">{$text}</td></tr>";
}, $data);
?>
<div prescr-count="{count}" style="display: none;">{count}</div>
<div><h3 style="margin-bottom: 10px;">Экспертный анамнез</h3></div>
<table style="display: block;">
	<?php echo implode("\n", $rows); ?>
</table>