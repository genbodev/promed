<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
	<s:Body>
		<AddStepToCase xmlns="http://tempuri.org/" xmlns:a="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.Case" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
			<guid>{guid}</guid>
			<idLpu>{IdLpu}</idLpu>
			<idPatientMis>{IdPatientMis}</idPatientMis>
			<idCaseMis>{IdCaseMis}</idCaseMis>
			<step i:type="b:StepAmb" xmlns:b="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.Step" >
				<b:DateStart>{DateStart}</b:DateStart>
				<b:DateEnd>{DateEnd}</b:DateEnd>
				<b:IdPaymentType>{IdPaymentType}</b:IdPaymentType>
				<b:Doctor xmlns:c="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
					<c:Person>
						<c:HumanName>
							<c:GivenName>{Doctor/GivenName}</c:GivenName>
							<c:MiddleName>{Doctor/MiddleName}</c:MiddleName>
							<c:FamilyName>{Doctor/FamilyName}</c:FamilyName>
						</c:HumanName>
						<c:Sex>{Doctor/Sex}</c:Sex>
						<c:Birthdate>{Doctor/Birthdate}</c:Birthdate>
						<c:IdPersonMis>{Doctor/IdPersonMis}</c:IdPersonMis>
						<c:Documents>
							{Doctor/Documents}
							<c:IdentityDocument>
								<c:DocN>{DocN}</c:DocN>
								<c:DocS>{DocS}</c:DocS>
								<c:ExpiredDate>{ExpiredDate}</c:ExpiredDate>
								<c:IdDocumentType>{IdDocumentType}</c:IdDocumentType>
								<c:IdProvider i:nil="true"/>
								<c:IssuedDate>{IssuedDate}</c:IssuedDate>
								<c:ProviderName>{ProviderName]</c:ProviderName>
							</c:IdentityDocument>
							{/Doctor/Documents}
						</c:Documents>
					</c:Person>
					<c:IdLpu>{Doctor/IdLpu}</c:IdLpu>
					<c:IdSpeciality>{Doctor/IdSpeciality}</c:IdSpeciality>
					<c:IdPosition>{Doctor/IdPosition}</c:IdPosition>
				</b:Doctor>
				<b:IdStepMis>{IdStepMis}</b:IdStepMis>
				<b:IdVisitPlace>{IdVisitPlace}</b:IdVisitPlace>
				<b:IdVisitPurpose>{IdVisitPurpose}</b:IdVisitPurpose>
				<b:MedRecords xmlns:mr="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec">
					{MedRecords}
				</b:MedRecords>
			</step>
		</AddStepToCase>
	</s:Body>
</s:Envelope>