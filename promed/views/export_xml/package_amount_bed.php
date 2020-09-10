<?php
$region = getRegionNick();
?>

<?php if ($region == 'krym') { ?>
<AMOUNT_BED>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DATE_BEGIN}
		{DATE_END}
		{FEMALE_BED}
		{MALE_BED}
		{BEDS_COUNT}
		{REPAIR_BED}
		{PLANNED_BEDS_COUNT}
		{PLANNED_MALE_BED}
		{PLANNED_FEMALE_BED}
		{DIVISION_ID}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
	</body><?php } ?>
</AMOUNT_BED>
<?php } else { ?>
<AMOUNT_BED>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DATE_BEGIN}
		{DATE_END}
		{FEMALE_BED}
		{MALE_BED}
		{BEDS_COUNT}
		{REPAIR_BED}
		{PLANNED_BEDS_COUNT}
		{PLANNED_MALE_BED}
		{PLANNED_FEMALE_BED}
		{DLSB}
	</body><?php } ?>
</AMOUNT_BED>
<?php } ?>