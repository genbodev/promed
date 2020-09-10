<link rel='stylesheet' type='text/css' href='/css/er.css?r=<?php echo rand(1,100000); ?>' />
<style>
	<?php
	loadLibrary('TTimetableTypes');
	foreach (TTimetableTypes::instance()->getTypes(null, true) as $type) {
		echo "#timeTable tr.time td.TimetableType_".$type->id." { ".$type->getStyle()." }";
		echo "#timeTable tr.time td.TimetableType_".$type->id."_person { ".$type->getStyle(1)." }";
	}
	?>
</style>