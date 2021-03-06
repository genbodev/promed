<mr:MedRecord i:type="mrd:PacsResult" xmlns:mrd="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec.MedDoc">
	<mrd:CreationDate>{CreationDate}</mrd:CreationDate>
	<mrd:Author>
		<mrd:Doctor xmlns:emk="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
			<emk:Person>
				<emk:HumanName>
					<emk:GivenName>{GivenName}</emk:GivenName>
					<emk:MiddleName>{MiddleName}</emk:MiddleName>
					<emk:FamilyName>{FamilyName}</emk:FamilyName>
				</emk:HumanName>
				<emk:Sex>{Sex}</emk:Sex>
				<emk:Birthdate>{Birthdate}</emk:Birthdate>
				<emk:IdPersonMis>{IdPersonMis}</emk:IdPersonMis>
				<emk:Documents>
					{Documents}
					<emk:IdentityDocument>
						<emk:DocN>{DocN}</emk:DocN>
						<emk:DocS>{DocS}</emk:DocS>
						<emk:ExpiredDate>{ExpiredDate}</emk:ExpiredDate>
						<emk:IdDocumentType>{IdDocumentType}</emk:IdDocumentType>
						<emk:IdProvider i:nil="true"/>
						<emk:IssuedDate>{IssuedDate}</emk:IssuedDate>
						<emk:ProviderName>{ProviderName}</emk:ProviderName>
					</emk:IdentityDocument>
					{/Documents}
				</emk:Documents>
			</emk:Person>
			<emk:IdLpu>{IdLpu}</emk:IdLpu>
			<emk:IdSpeciality>{IdSpeciality}</emk:IdSpeciality>
			<emk:IdPosition>{IdPosition}</emk:IdPosition>
		</mrd:Doctor>
	</mrd:Author>
	<mrd:Header>{Header}</mrd:Header>
	<mrd:IdDocumentMis>{IdDocumentMis}</mrd:IdDocumentMis>
	<mrd:Code>{Code}</mrd:Code>
	<mrd:Header>{Header}</mrd:Header>
	<mrd:Image>
		<mrd:UID>{UID}</mrd:UID>
		<mrd:PACS>{PACS}</mrd:PACS>
		<mrd:Report>{Report}</mrd:Report>
		<mrd:Description>{Description}</mrd:Description>
		<mrd:Conclusion>{Conclusion}</mrd:Conclusion>
	</mrd:Image>
	<mrd:Attachment>
		<mrd:Data>{Data}</mrd:Data>
		<mrd:MIMEType>{MIMEType}</mrd:MIMEType>
	</mrd:Attachment>
</mr:MedRecord>