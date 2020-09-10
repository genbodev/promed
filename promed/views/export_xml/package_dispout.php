<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<DN_OUT>
	<ZGLV>
		<VERSION>1.0</VERSION>
		{DATA}
		{CODE_MO}
		{TYPE}
		{ID}
	</ZGLV>
	<?php if($TYPE!='Delete'){ ?><DIRECT>
		{ID_PAC}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{VPOLIS}
		{SPOLIS}
		{NPOLIS}
		{DATE_OUT}
		{DS_DISP}
		{SNILS_VR}
		{RESULT_OUT}
	</DIRECT><?php } ?>
</DN_OUT>
<?php } else if ($isTFOMSAutoInteract && $region == 'ufa') { ?>
<DISPOUT>
	<HEADER>
		{OPERATIONTYPE}
		{CODE_MO}
		{DATA}
		{DISP_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{BDZ_GUID}
		{ID_PAC}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{VPOLIS}
		{SPOLIS}
		{NPOLIS}
		{DS}
		{SNILS_VR}
		{DATE_OUT}
		{RESULT_OUT}
	</BODY><?php } ?>
</DISPOUT>
<?php } else if ($isTFOMSAutoInteract) { ?>
<DISPOUT>
	<HEADER>
		{OPERATIONTYPE}
		{CODE_MO}
		{DATA}
		{DISP_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{BDZ_ID}
		{ID_PAC}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{VPOLIS}
		{SPOLIS}
		{NPOLIS}
		{DS}
		{SNILS_VR}
		{DATE_OUT}
		{RESULT_OUT}
	</BODY><?php } ?>
</DISPOUT>
<?php } ?>