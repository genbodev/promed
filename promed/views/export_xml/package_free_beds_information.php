<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<FREE_BEDS_INFORMATION>
	<ZGLV>
		{DATA}
		{TYPE}
		{ID}
	</ZGLV>
	<?php if($TYPE!='Delete'){ ?><ZAP>
		{DTA_RAB}
		{MCOD_STC}
		{MPODR_STC}
		{USL_OK}
		{KOD_PFK}
		{KOL_PAC}
		{KOL_IN}
		{KOL_OUT}
		{KOL_PLN}
		{KOL_PUS}
		{KOL_PUS_V}
		{KOL_PUS_D}
	</ZAP><?php } ?>
</FREE_BEDS_INFORMATION>
<?php } else if ($isTFOMSAutoInteract) { ?>
<FREE_BEDS_INFORMATION>
	<HEADER>
		{OPERATIONTYPE}
		{DATA}
		{CODE_MO}
		{FBI_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{ACTUAL_DATE}
		{BRANCH}
		{DIVISIONPROFIL}
		{BEDPROFIL}
		{CARETYPE}
		{BEDOCCUPIED}
		{BEDOCCUPIEDTODAY}
		{BEDCLEARTODAY}
		{BEDPLANNED}
		{BEDFREE}
		{BEDFREEADULT}
		{BEDFREECHILD}
	</BODY><?php } ?>
</FREE_BEDS_INFORMATION>
<?php } else if ($region == 'krym') { ?>
<FREE_BEDS_INFORMATION>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{ACTUAL_DATE}
		{AMOUNT}
		{DIVISION_ID}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
	</body><?php } ?>
</FREE_BEDS_INFORMATION>
<?php } else { ?>
<FREE_BEDS_INFORMATION>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{ACTUAL_DATE}
		{AMOUNT}
		{DLSB}
	</body><?php } ?>
</FREE_BEDS_INFORMATION>
<?php } ?>