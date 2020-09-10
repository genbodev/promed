<wsse:Security soapenv:actor="{id}" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
	<wsse:BinarySecurityToken
		EncodingType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary"
		ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3"
		wsu:Id="">{BinarySecurityToken}</wsse:BinarySecurityToken>
	<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">
		<SignedInfo>
			<CanonicalizationMethod
				Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#WithComments" />
			<SignatureMethod Algorithm="urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34102012-gostr34112012-256" />
			<Reference URI="">
				<DigestMethod Algorithm="urn:ietf:params:xml:ns:cpxmlsec:algorithms:gostr34112012-256" />
				<DigestValue>{DigestValue}</DigestValue>
			</Reference>
		</SignedInfo>
		<SignatureValue>{SignatureValue}</SignatureValue>
		<KeyInfo>
			<wsse:SecurityTokenReference>
				<wsse:Reference URI=""
					ValueType="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-x509-token-profile-1.0#X509v3" />
			</wsse:SecurityTokenReference>
		</KeyInfo>
	</Signature>
</wsse:Security>