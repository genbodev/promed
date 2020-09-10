<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<ATTACH_DATA>
	<ZGLV>
		<VERSION>1.0</VERSION>
		<DATE>{DATA}</DATE>
		{CODE_MO}
		{ID_MO}
	</ZGLV>
	<PERS>
		{ID_PAC}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{SMO}
		{VPOLIS}
		{SPOLIS}
		{NPOLIS}
		{OPER_TYPE}
		{INFO_TYPE}
		{DATE}
		{SP_PRIK}
		{T_PRIK}
		{KOD_PODR}
		{NUM_UCH}
		{TIP_UCH}
		{SNILS_VR}
		{PHONE1}
		{PHONE2}
	</PERS>
</ATTACH_DATA>
<?php } else if ($isTFOMSAutoInteract && $region == 'perm') { ?>
<PERSONATTACH>
	<HEADER>
		{OPERATIONTYPE}
		{CODE_MO}
		{DATA}
		{PERSONATTACHID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{BDZID}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{DOCTYPE}
		{DOCSER}
		{DOCNUM}
		{SNILS}
		{ATTACH_TYPE}
		{ATTACH_DT_MO}
		{DETACH_DT_MO}
		{DETACH_CAUSE_MO}
		{PODR}
		{OTD}
		{UCH}
		{PUNKT}
		{SNILS_VR}
		{ATTACH_DT}
		{PODR_F}
		{OTD_F}
		{UCH_F}
		{PUNKT_F}
		{SNILS_VR_F}
		{ATTACH_DT_F}
		{DETACH_DT_F}
		{DETACH_F_CAUSE}
	</BODY><?php } ?>
</PERSONATTACH>
<?php } else if ($isTFOMSAutoInteract) { ?>
<PERSONATTACHDISTRICT>
	<HEADER>
		{OPERATIONTYPE}
		{DATA}
		{CODE_MO}
		{ID_MO}
		{PERSONATTACHID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{BDZID}
		{ID_PAC}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{SMO}
		{VPOLIS}
		{SPOLIS}
		{NPOLIS}
		{DOCTYPE}
		{DOCSER}
		{DOCNUM}
		{SNILS}
		{INFO_TYPE}
		{DATE}
		{ATTACH_TYPE}
		{T_PRIK}
		{ATTACH_DT_MO}
		{ATTACH_CODE_MO}
		{DETACH_DT_MO}
		{DETACH_CAUSE_MO}
		{PODR}
		{OTD}
		{UCH}
		{UCH_TYPE}
		{PUNKT}
		{SNILS_VR}
		{ATTACH_DT}
		{PODR_F}
		{OTD_F}
		{UCH_F}
		{PUNKT_F}
		{SNILS_VR_F}
		{ATTACH_DT_F}
		{DETACH_DT_F}
		{DETACH_F_CAUSE}
		{PHONE1}
		{PHONE2}
	</BODY><?php } ?>
</PERSONATTACHDISTRICT>
<?php } ?>