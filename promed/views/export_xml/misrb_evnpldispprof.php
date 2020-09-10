<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
	<s:Body>
		<{function} xmlns="http://tempuri.org/">
			<guid>{guid}</guid>
			<{casedto} i:type="a:CaseAmb" xmlns:a="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.Case" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
				<a:OpenDate>{OpenDate}</a:OpenDate>
				<a:CloseDate>{CloseDate}</a:CloseDate>
				<a:HistoryNumber>{HistoryNumber}</a:HistoryNumber>
				<a:IdCaseMis>{IdCaseMis}</a:IdCaseMis>
				<a:IdPaymentType>{IdPaymentType}</a:IdPaymentType>
				<a:Confidentiality>{Confidentiality}</a:Confidentiality>
				<a:DoctorConfidentiality>{DoctorConfidentiality}</a:DoctorConfidentiality>
				<a:CuratorConfidentiality>{CuratorConfidentiality}</a:CuratorConfidentiality>
				<a:IdLpu>{IdLpu}</a:IdLpu>
				<a:IdCaseResult>{IdCaseResult}</a:IdCaseResult>
				<a:Comment>{Comment}</a:Comment>
				<a:DoctorInCharge xmlns:b="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
					<b:Person>
						<b:HumanName>
							<b:GivenName>{DoctorInCharge/GivenName}</b:GivenName>
							<b:MiddleName>{DoctorInCharge/MiddleName}</b:MiddleName>
							<b:FamilyName>{DoctorInCharge/FamilyName}</b:FamilyName>
						</b:HumanName>
						<b:Sex>{DoctorInCharge/Sex}</b:Sex>
						<b:Birthdate>{DoctorInCharge/Birthdate}</b:Birthdate>
						<b:IdPersonMis>{DoctorInCharge/IdPersonMis}</b:IdPersonMis>
						<b:Documents>
							{DoctorInCharge/Documents}
							<b:IdentityDocument>
								<b:DocN>{DocN}</b:DocN>
								<b:DocS>{DocS}</b:DocS>
								<b:ExpiredDate>{ExpiredDate}</b:ExpiredDate>
								<b:IdDocumentType>{IdDocumentType}</b:IdDocumentType>
								<b:IdProvider i:nil="true"/>
								<b:IssuedDate>{IssuedDate}</b:IssuedDate>
								<b:ProviderName>{ProviderName}</b:ProviderName>
							</b:IdentityDocument>
							{/DoctorInCharge/Documents}
						</b:Documents>
					</b:Person>
					<b:IdLpu>{DoctorInCharge/IdLpu}</b:IdLpu>
					<b:IdSpeciality>{DoctorInCharge/IdSpeciality}</b:IdSpeciality>
					<b:IdPosition>{DoctorInCharge/IdPosition}</b:IdPosition>
				</a:DoctorInCharge>
				<a:Authenticator xmlns:b="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
					<b:Doctor>
						<b:Person>
							<b:HumanName>
								<b:GivenName>{Authenticator/GivenName}</b:GivenName>
								<b:MiddleName>{Authenticator/MiddleName}</b:MiddleName>
								<b:FamilyName>{Authenticator/FamilyName}</b:FamilyName>
							</b:HumanName>
							<b:Sex>{Authenticator/Sex}</b:Sex>
							<b:Birthdate>{Authenticator/Birthdate}</b:Birthdate>
							<b:IdPersonMis>{Authenticator/IdPersonMis}</b:IdPersonMis>
							<b:Documents>
								{Authenticator/Documents}
								<b:IdentityDocument>
									<b:DocN>{DocN}</b:DocN>
									<b:DocS>{DocS}</b:DocS>
									<b:ExpiredDate>{ExpiredDate}</b:ExpiredDate>
									<b:IdDocumentType>{IdDocumentType}</b:IdDocumentType>
									<b:IdProvider i:nil="true"/>
									<b:IssuedDate>{IssuedDate}</b:IssuedDate>
									<b:ProviderName>{ProviderName}</b:ProviderName>
								</b:IdentityDocument>
								{/Authenticator/Documents}
							</b:Documents>
						</b:Person>
						<b:IdLpu>{Authenticator/IdLpu}</b:IdLpu>
						<b:IdSpeciality>{Authenticator/IdSpeciality}</b:IdSpeciality>
						<b:IdPosition>{Authenticator/IdPosition}</b:IdPosition>
					</b:Doctor>
				</a:Authenticator>
				<a:Author xmlns:b="http://schemas.datacontract.org/2004/07/N3.EMK.Dto">
					<b:Doctor>
						<b:Person>
							<b:HumanName>
								<b:GivenName>{Author/GivenName}</b:GivenName>
								<b:MiddleName>{Author/MiddleName}</b:MiddleName>
								<b:FamilyName>{Author/FamilyName}</b:FamilyName>
							</b:HumanName>
							<b:Sex>{Author/Sex}</b:Sex>
							<b:Birthdate>{Author/Birthdate}</b:Birthdate>
							<b:IdPersonMis>{Author/IdPersonMis}</b:IdPersonMis>
							<b:Documents>
								{Author/Documents}
								<b:IdentityDocument>
									<b:DocN>{DocN}</b:DocN>
									<b:DocS>{DocS}</b:DocS>
									<b:ExpiredDate>{ExpiredDate}</b:ExpiredDate>
									<b:IdDocumentType>{IdDocumentType}</b:IdDocumentType>
									<b:IdProvider i:nil="true"/>
									<b:IssuedDate>{IssuedDate}</b:IssuedDate>
									<b:ProviderName>{ProviderName}</b:ProviderName>
								</b:IdentityDocument>
								{/Author/Documents}
							</b:Documents>
						</b:Person>
						<b:IdLpu>{Author/IdLpu}</b:IdLpu>
						<b:IdSpeciality>{Author/IdSpeciality}</b:IdSpeciality>
						<b:IdPosition>{Author/IdPosition}</b:IdPosition>
					</b:Doctor>
				</a:Author>
				<a:IdPatientMis>{IdPatientMis}</a:IdPatientMis>
				<a:IdCaseType>{IdCaseType}</a:IdCaseType>
				<a:Steps xmlns:b="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.Step">
					{Steps}
					<b:StepAmb>
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
					</b:StepAmb>
					{/Steps}
				</a:Steps>
				<a:MedRecords xmlns:mr="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec">
					{MedRecords}
				</a:MedRecords>
			</{casedto}>
		</{function}>
	</s:Body>
</s:Envelope>