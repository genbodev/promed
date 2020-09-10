<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<S:Header xmlns:S="http://schemas.xmlsoap.org/soap/envelope/">
		<wsse:Security xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" S:actor="http://eln.fss.ru/actor/mo/{ogrn}">
			<ds:Signature>
				<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">
					<CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
					<SignatureMethod Algorithm="{signatureMethod}"/>
					<Reference URI="#OGRN_{ogrn}">
						<Transforms>
							<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
						</Transforms>
						<DigestMethod Algorithm="{digestMethod}"/>
						<DigestValue>{filehash}</DigestValue>
					</Reference></SignedInfo>
				<SignatureValue xmlns="http://www.w3.org/2000/09/xmldsig#">{filesign}</SignatureValue>
				<ds:KeyInfo>
					<wsse:SecurityTokenReference>
						<wsse:Reference URI="#http://eln.fss.ru/actor/mo/{ogrn}"/></wsse:SecurityTokenReference>
				</ds:KeyInfo>
			</ds:Signature>
			<wsse:BinarySecurityToken EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary" ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" wsu:Id="http://eln.fss.ru/actor/mo/{ogrn}">{certbase64}</wsse:BinarySecurityToken>
		</wsse:Security>
	</S:Header>
	<soapenv:Body xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" wsu:Id="OGRN_{ogrn}">
		<getLNData xmlns="http://ru/ibs/fss/ln/ws/FileOperationsLn.wsdl">
			<ogrn>{ogrn}</ogrn>
			<lnCode>{lnCode}</lnCode>
			<snils>{snils}</snils>
		</getLNData>
	</soapenv:Body>
</soapenv:Envelope>