<mr:MedRecord i:type="mrd:DischargeSummary" xmlns:mrd="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec.MedDoc">
	<mrd:CreationDate>{CreationDate}</mrd:CreationDate>
	<mrd:Author xmlns:doc="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
		<doc:Person>
			<doc:HumanName>
				<doc:GivenName>{GivenName}</doc:GivenName>
				<doc:MiddleName>{MiddleName}</doc:MiddleName>
				<doc:FamilyName>{FamilyName}</doc:FamilyName>
			</doc:HumanName>
			<doc:Sex>{Sex}</doc:Sex>
			<doc:Birthdate>{Birthdate}</doc:Birthdate>
			<doc:IdPersonMis>{IdPersonMis}</doc:IdPersonMis>
			<doc:Documents>
				{Documents}
				<doc:IdentityDocument>
					<doc:DocN>{DocN}</doc:DocN>
					<doc:DocS>{DocS}</doc:DocS>
					<doc:ExpiredDate>{ExpiredDate}</doc:ExpiredDate>
					<doc:IdDocumentType>{IdDocumentType}</doc:IdDocumentType>
					<doc:IdProvider i:nil="true"/>
					<doc:IssuedDate>{IssuedDate}</doc:IssuedDate>
					<doc:ProviderName>{ProviderName}</doc:ProviderName>
				</doc:IdentityDocument>
				{/Documents}
			</doc:Documents>
		</doc:Person>
		<doc:IdLpu>{IdLpu}</doc:IdLpu>
		<doc:IdSpeciality>{IdSpeciality}</doc:IdSpeciality>
		<doc:IdPosition>{IdPosition}</doc:IdPosition>
	</mrd:Author>
	<mrd:Header>{Header}</mrd:Header>
	<mrd:Attachment>
		<mrd:Data>{Data}</mrd:Data>
		<mrd:MIMEType>{MIMEType}</mrd:MIMEType>
	</mrd:Attachment>
</mr:MedRecord>