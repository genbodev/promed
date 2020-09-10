<patient>
	<snils>{snils}</snils>
	<lastName>{lastName}</lastName>
	<firstName>{firstName}</firstName>
	<patronymic>{patronymic}</patronymic>
	<gender>{gender}</gender>
	<birthDate>{birthDate}</birthDate>
	<workPlace>{workPlace}</workPlace>
	<citizenShipId<?php echo empty($citizenShipId)?'':' id="{citizenShipId}" ';?>/>
	<noresidentStatusId<?php echo empty($noresidentStatusId)?'':' id="{noresidentStatusId}" ';?>/>
	<decreedGroupId<?php echo empty($decreedGroupId)?'':' id="{decreedGroupId}" ';?>/>
	<socStatusId<?php echo empty($socStatusId)?'':' id="{socStatusId}" ';?>/>
	<relatives>
		{relatives}
		<relative>
			<lastName>{lastName}</lastName>
			<firstName>{firstName}</firstName>
			<patronymic>{patronymic}</patronymic>
			<address>
				<aoidArea>{addressAoidArea}</aoidArea>
				<aoidStreet>{addressAoidStreet}</aoidStreet>
				<houseid>{addressHouseid}</houseid>
				<region id="{addressRegion}"/>
				<areaName>{addressAreaName}</areaName>
				<prefixArea>{addressPrefixArea}</prefixArea>
				<streetName>{addressStreetName}</streetName>
				<prefixStreet>{addressPrefixStreet}</prefixStreet>
				<house>{addressHouse}</house>
				<flat>{addressFlat}</flat>
			</address>
			<phone>{phone}</phone>
		</relative>
		{/relatives}
	</relatives>
	<documents>
		<?php if (!empty($documentId)) { ?>
		<document>
			<serial>{documentSerial}</serial>
			<number>{documentNumber}</number>
			<passDate>{documentPassDate}</passDate>
			<passOrg>{documentPassOrg}</passOrg>
			<documentId id="{documentId}"/>
		</document>
		<?php } ?>
	</documents>
</patient>
