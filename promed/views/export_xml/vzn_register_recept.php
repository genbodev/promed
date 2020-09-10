<record type="create">
	<createVznRecipe>
		<registryNumber>{registryNumber}</registryNumber>
		<recipe>
			<recipeSerial>{recipeSerial}</recipeSerial>
			<recipeNumber>{recipeNumber}</recipeNumber>
			<issueDate>{issueDate}</issueDate>
			<issueDosageId<?php echo empty($issueDosageId)?'':' id="{issueDosageId}" ';?>/>
			<doseCount>{doseCount}</doseCount>
			<personId>{personId}</personId>
			<desease>{desease}</desease>
			<mnnId<?php echo empty($mnnId)?'':' id="{mnnId}" ';?>/>
			<moId>{moId}</moId>
			<drugFormId <?php echo empty($drugFormId)?'':' id="{drugFormId}" ';?>/>
			<isTheraphyResistence>{isTheraphyResistence}</isTheraphyResistence>
			<territoryId<?php echo empty($territoryId)?'':' id="{territoryId}" ';?>/>
			<deliveryDate>{deliveryDate}</deliveryDate>
			<dosageId<?php echo empty($dosageId)?'':' id="{dosageId}" ';?>/>
			<doseInPack>{doseInPack}</doseInPack>
			<packCount>{packCount}</packCount>
			<pharmacyId>{pharmacyId}</pharmacyId>
			<vznDrugId<?php echo empty($vznDrugId)?'':' id="{vznDrugId}" ';?>/>
			<signedPerson>{signedPerson}</signedPerson>
			<note>{note}</note>
		</recipe>
	</createVznRecipe>
</record>