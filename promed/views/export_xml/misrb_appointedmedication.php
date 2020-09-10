<mr:MedRecord i:type="mrd:AppointedMedication" xmlns:mrd="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec">
	<mrd:AnatomicTherapeuticChemicalClassification>{AnatomicTherapeuticChemicalClassification}</mrd:AnatomicTherapeuticChemicalClassification>
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
	<mrd:IssuedDate>{IssuedDate}</mrd:IssuedDate>
	<mrd:MedicineIssueType>{MedicineIssueType}</mrd:MedicineIssueType>
	<mrd:MedicineName>{MedicineName}</mrd:MedicineName>
	<mrd:Number>{Number}</mrd:Number>
	<mrd:Seria>{Seria}</mrd:Seria>
</mr:MedRecord>