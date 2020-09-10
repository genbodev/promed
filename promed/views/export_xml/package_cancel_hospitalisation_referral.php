<?php
$isTFOMSAutoInteract = isset($isTFOMSAutoInteract)?$isTFOMSAutoInteract:false;
$region = getRegionNick();
?>

<?php if ($isTFOMSAutoInteract && $region == 'kareliya') { ?>
<CANCEL_HOSPITALISATION_REFERRAL>
	<ZGLV>
		{DATA}
		{TYPE}
		{ID}
	</ZGLV>
	<ZAP>
		{NOM_NAP}
		{DTA_NAP}
		{MCOD_NAP}
		{IST_ANL}
		{ACOD}
		{MPODR_ANL}
		{PR_ANL}
	</ZAP>
</CANCEL_HOSPITALISATION_REFERRAL>
<?php } else if ($isTFOMSAutoInteract) { ?>
<CANCEL_HOSPITALISATION_REFERRAL>
	<HEADER>
		{OPERATIONTYPE}
		{DATA}
		{CODE_MO}
		{CODE_MO_TO}
		{CHR_ID}
	</HEADER>
	<?php if($OPERATIONTYPE!='Delete'){ ?><BODY>
		{REFERRAL_NUMBER}
		{DATE}
		{REFERRAL_LPU}
		{BRANCH}
		{REASON}
		{CANCEL_SOURSE}
		{CANCEL_CODE}
		{DATE_CANCEL}
		{CANCEL_TYPE}
		{CANCEL_DESCRIPTION}
		{PATIENT}
	</BODY><?php } ?>
</CANCEL_HOSPITALISATION_REFERRAL>
<?php } else { ?>
<CANCEL_HOSPITALISATION_REFERRAL>
	<header>
		{LPU}
		{TYPE}
		{LPU_TO}
		{ID}
	</header>
	<?php if($TYPE!='Delete'){ ?><body>
		{REFERRAL_NUMBER}
		{DATE}
		{REFERRAL_LPU}
		{BRANCH}
		{REASON}
		{CANCEL_SOURSE}
		{DATE_CANCEL}
		{CANCEL_TYPE}
		{CANCEL_DESCRIPTION}
		{PATIENT}
	</body><?php } ?>
</CANCEL_HOSPITALISATION_REFERRAL>
<?php } ?>