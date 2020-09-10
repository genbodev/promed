<mr:MedRecord i:type="mrd:Diagnosis" xmlns:mrd="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec.Diag">
	<mrd:DiagnosisInfo>
		<mrd:IdDiseaseType>{IdDiseaseType}</mrd:IdDiseaseType>
		<mrd:DiagnosedDate>{DiagnosedDate}</mrd:DiagnosedDate>
		<mrd:IdDiagnosisType>{IdDiagnosisType}</mrd:IdDiagnosisType>
		<mrd:Comment>{Comment}</mrd:Comment>
		<mrd:MkbCode>{MkbCode}</mrd:MkbCode>
	</mrd:DiagnosisInfo>
	<mrd:Doctor xmlns:doc="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
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
	</mrd:Doctor>
</mr:MedRecord>