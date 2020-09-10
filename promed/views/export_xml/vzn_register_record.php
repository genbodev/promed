<record type="{type}">
	<?php echo "<{$type}Vzn>"; ?>
		<key>
			<snils>{snils}</snils>
		</key>
		<moId>{moId}</moId>
		<includeDate>{includeDate}</includeDate>
		<?php if ($type == 'update') { ?><excludeDate>{excludeDate}</excludeDate><?php } ?>
		<registry>
			<?php if ($type == 'update') { ?><registryNumber>{registryNumber}</registryNumber><?php } ?>
			<birthLastName>{birthLastName}</birthLastName>
			<policTypeId<?php echo empty($policTypeId)?'':' id="{policTypeId}" ';?>/>
			<policSerial>{policSerial}</policSerial>
			<policNumber>{policNumber}</policNumber>
			<imcCode>{imcCode}</imcCode>
			<?php if ($type == 'update') { ?><deathDate>{deathDate}</deathDate><?php } ?>
			<isInclRegistry>{isInclRegistry}</isInclRegistry>
			<isDrugSupply>{isDrugSupply}</isDrugSupply>
			<deseases>
				<desease>{desease}</desease>
			</deseases>
			<addresses>
				{addresses}
				<patientAddress>
					<addressTypeId id="{addressTypeId}"/>
					<address>
						<aoidArea>{aoidArea}</aoidArea>
						<aoidStreet>{aoidStreet}</aoidStreet>
						<houseid>{houseid}</houseid>
						<region id="{region}"/>
						<areaName>{areaName}</areaName>
						<prefixArea>{prefixArea}</prefixArea>
						<streetName>{streetName}</streetName>
						<prefixStreet>{prefixStreet}</prefixStreet>
						<house>{house}</house>
						<flat>{flat}</flat>
					</address>
				</patientAddress>
				{/addresses}
			</addresses>
			<disabilityGroupId<?php echo empty($disabilityGroupId)?'':' id="{disabilityGroupId}" ';?>/>
			<?php if ($type == 'update') { ?><registryOperationId<?php echo empty($registryOperationId)?'':' id="{registryOperationId}" ';?>/><?php } ?>
			<?php if ($type == 'update') { ?><excludeReasonId<?php echo empty($excludeReasonId)?'':' id="{excludeReasonId}" ';?>/><?php } ?>
			<?php if ($type == 'update') { ?><excludeMoId>{excludeMoId}</excludeMoId><?php } ?>
			<territoryId<?php echo empty($territoryId)?'':' id="{territoryId}" ';?>/>
			<?php if ($type == 'update') { ?><signedPerson>{signedPerson}</signedPerson><?php } ?>
		</registry>
	<?php echo "</{$type}Vzn>"; ?>
</record>