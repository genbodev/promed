<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>
<?php if ($region == 'buryatiya') { ?>
<DIVISION>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DV_CODE}
		{NAME}
		{FULLNAME}
		{LEVEL}
		{CODE_MZ}
		{PHONE}
		{ADDRESS}
		{BRANCH_ID}
		{DIVISION_TYPE}
		{REVENUE_TYPE}
		{ID_NUMBER_CARD}
		{ACTIVE}
	</body><?php } ?>
</DIVISION>
<?php } else { ?>
<DIVISION>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DV_CODE}
		{NAME}
		{FULLNAME}
		{LEVEL}
		{CODE_MZ}
		{PHONE}
		{ADDRESS}
		{BRANCH_ID}
		{DIVISION_TYPE}
		{REVENUE_TYPE}
		{ACTIVE}
		{ID_NUMBER_CARD}
	</body><?php } ?>
</DIVISION>
<?php } ?>
