<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<DN_IN>
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
		{PHONE}
		{DATE_IN}
		{DS_DISP}
		{SNILS_VR}
		{KRAT}
		{DN_MONTH1}
		{DN_MONTH2}
		{DN_MONTH3}
		{DN_MONTH4}
		{DN_MONTH5}
		{DN_MONTH6}
		{DN_MONTH8}
		{DN_MONTH9}
		{DN_MONTH10}
		{DN_MONTH11}
		{DN_MONTH12}
		{DN_PLACE}
	</DIRECT><?php } ?>
</DN_IN>
<?php } else if ($isTFOMSAutoInteract && $region == 'perm') { ?>
<DISP>
	<HEADER>
		{OPERATIONTYPE}
		{CODE_MO}
		{DISP_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{BDZ_ID}
		{FAM}
		{IM}
		{OT}
		{W}
		{DR}
		{DOCTYPE}
		{DOCSER}
		{DOCNUM}
		{SNILS}
		{ATTACH_DISP_TYPE}
		{DATE_IN}
		{DS}
		{DS_DETECT}
		{DS_DETECTTYPE}
		{SNILS_VR}
		{DATE_OUT}
		{RESULT_OUT}
		<DATES>
			{DATES}{PLAN_DATE}{/DATES}
		</DATES>
	</BODY><?php } ?>
</DISP>
<?php } else if ($isTFOMSAutoInteract && $region == 'ufa') { ?>
<DISP>
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
		{DOCTYPE}
		{DOCSER}
		{DOCNUM}
		{SNILS}
		{ATTACH_DISP_TYPE}
		{DATE_IN}
		{DS}
		{DS_DETECT}
		{DS_DETECTTYPE}
		{SNILS_VR}
		{DATE_OUT}
		{RESULT_OUT}
		<DATES>
			{DATES}{PLAN_DATE}{/DATES}
		</DATES>
	</BODY><?php } ?>
</DISP>
<?php } else if ($isTFOMSAutoInteract) { ?>
<DISP>
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
		{DOCTYPE}
		{DOCSER}
		{DOCNUM}
		{SNILS}
		{ATTACH_DISP_TYPE}
		{DATE_IN}
		{DS}
		{DS_DETECT}
		{DS_DETECTTYPE}
		{SNILS_VR}
		{DATE_OUT}
		{RESULT_OUT}
		<DATES>
			{DATES}{PLAN_DATE}{/DATES}
		</DATES>
	</BODY><?php } ?>
</DISP>
<?php } ?>