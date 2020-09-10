<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($region == 'buryatiya') { ?>
<DIVISION_LINK_STRUCTURE_BED>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DIVISION_ID}
		{V020_STRUCTURE_BED}
		{STRUCTURE_BED}
	</body><?php } ?>
</DIVISION_LINK_STRUCTURE_BED>
<?php } else { ?>
<DIVISION_LINK_STRUCTURE_BED>
	<header>
		{LPU}
		{TYPE}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{DIVISION_ID}
		{STRUCTURE_BED}
		{V020_STRUCTURE_BED}
	</body><?php } ?>
</DIVISION_LINK_STRUCTURE_BED>
<?php } ?>