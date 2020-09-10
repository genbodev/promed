<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>
<?php if ($region == 'buryatiya') { ?>
<BRANCH>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{BR_CODE}
		{NAME}
		{DATE_ADD}
		{ADDRESS}
		{LPU_TYPE}
		{CATEGORY}
		{PHONE}
		{ACTIVE}
		{DATE_REMOVE}
	</body><?php } ?>
</BRANCH>
<?php } else { ?>
<BRANCH>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{BR_CODE}
		{NAME}
		{DATE_ADD}
		{ADDRESS}
		{LPU_TYPE}
		{CATEGORY}
		{PHONE}
		{ACTIVE}
		{DATE_REMOVE}
	</body><?php } ?>
</BRANCH>
<?php } ?>
