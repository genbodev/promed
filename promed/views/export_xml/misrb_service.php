<mr:MedRecord i:type="mrd:Service" xmlns:mrd="http://schemas.datacontract.org/2004/07/N3.EMK.Dto.MedRec">
	<mrd:DateEnd>{DateEnd}</mrd:DateEnd>
	<mrd:DateStart>{DateStart}</mrd:DateStart>
	<mrd:IdServiceType>{IdServiceType}</mrd:IdServiceType>
	<mrd:ServiceName>{ServiceName}</mrd:ServiceName>
	<mrd:Performer>
		<mrd:IdRole>{IdRole}</mrd:IdRole>
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
	</mrd:Performer>
	<mrd:PaymentInfo>
		<mrd:IdPaymentType>{IdPaymentType}</mrd:IdPaymentType>
		<mrd:PaymentState>{PaymentState}</mrd:PaymentState>
		<mrd:HealthCareUnit>{HealthCareUnit}</mrd:HealthCareUnit>
		<mrd:Quantity>{Quantity}</mrd:Quantity>
		<mrd:Tariff>{Tariff}</mrd:Tariff>
	</mrd:PaymentInfo>
</mr:MedRecord>