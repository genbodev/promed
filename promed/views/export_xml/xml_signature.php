<wsse:Security soapenv:actor="{id}">
	<wsse:BinarySecurityToken
		EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary"
		ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"
		wsu:Id="{id}">{BinarySecurityToken}</wsse:BinarySecurityToken>
	<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
		<SignedInfo>
			<CanonicalizationMethod
				Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315#WithComments" />
			<SignatureMethod Algorithm="{signatureMethod}"/>
			<Reference URI="#{block}">
				<DigestMethod Algorithm="{digestMethod}"/>
				<DigestValue>{DigestValue}</DigestValue>
			</Reference>
		</SignedInfo>
		<SignatureValue>{SignatureValue}</SignatureValue>
		<KeyInfo>
			<wsse:SecurityTokenReference>
				<wsse:Reference URI="#{id}"
					ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" />
			</wsse:SecurityTokenReference>
		</KeyInfo>
	</Signature>
</wsse:Security>