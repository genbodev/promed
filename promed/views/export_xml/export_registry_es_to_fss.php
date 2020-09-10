<env:Envelope xmlns:env="http://schemas.xmlsoap.org/soap/envelope/" xmlns="http://ru/fss/ln/ws.wsdl" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ns1="http://smev.gosuslugi.ru/rev120315" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
	<env:Header>
		<wsse:Security env:actor="http://smev.gosuslugi.ru/actors/smev">
			<wsse:BinarySecurityToken EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wsu:Id="CertId-{certhash}">
			{certbase64}
			</wsse:BinarySecurityToken>
			<ds:Signature xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
				<ds:SignedInfo>
					<ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
					<ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#gostr34102001-gostr3411"/>
					<ds:Reference URI="#body">
						<ds:Transforms>
							<ds:Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
						</ds:Transforms>
						<ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#gostr3411"/>
						<ds:DigestValue>{filehash}</ds:DigestValue>
					</ds:Reference>
				</ds:SignedInfo>
				<ds:SignatureValue>{filesign}</ds:SignatureValue>

				<ds:KeyInfo>
					<wsse:SecurityTokenReference>
						<wsse:Reference URI="#CertId-{certhash}" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"/>
					</wsse:SecurityTokenReference>
				</ds:KeyInfo>
			</ds:Signature>
		</wsse:Security>
	</env:Header>
	<env:Body wsu:Id="body">
		<ns1:saveLn>
			<ns1:Message>
				<ns1:Sender>
					<ns1:Code>FSSR01001</ns1:Code>
					<ns1:Name>ФСС России</ns1:Name>
				</ns1:Sender>
				<ns1:Recipient>
					<ns1:Code>FSSR01001</ns1:Code>
					<ns1:Name>ФСС России</ns1:Name>
				</ns1:Recipient>
				<ns1:ServiceName/>
				<ns1:TypeCode>GFNC</ns1:TypeCode>
				<ns1:Status>REQUEST</ns1:Status>
				<ns1:Date>2013-09-13T15:01:43.468+04:00</ns1:Date>
				<ns1:ExchangeType>2</ns1:ExchangeType>
				<ns1:TestMsg>Тестирование Астраханской области</ns1:TestMsg>
			</ns1:Message>
			<ns1:MessageData>
				<ns1:AppData>
					<saveLnRequest>
						<ogrn>{ogrn}</ogrn>
						<inn>{inn}</inn>
						<licenseNumber>{licenseNumber}</licenseNumber>

						{lnInfo}
						<lnInfo>
							<lnCode>{lnCode}</lnCode>
						</lnInfo>
						{/lnInfo}

					</saveLnRequest>
				</ns1:AppData>
				<ns1:AppDocument>
					<ns1:RequestCode/>
					<ns1:BinaryData>{BinaryData}</ns1:BinaryData>
				</ns1:AppDocument>
			</ns1:MessageData>
		</ns1:saveLn>
	</env:Body>
</env:Envelope>
