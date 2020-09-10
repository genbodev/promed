<?xml version="1.0"?>
<xsl:stylesheet 
version="1.0" 
xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
xmlns:n1="urn:hl7-org:v3" 
xmlns:n2="urn:hl7-org:v3/meta/voc" 
xmlns:voc="urn:hl7-org:v3/voc" 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:f88="urn:f88">
	<xsl:output method="html" indent="yes" version="4.01" encoding="utf-8" doctype-public="-//W3C//DTD HTML 4.01//EN"/>
	<!-- CDA document -->
	<xsl:variable name="tableWidth">50%</xsl:variable>
	<xsl:variable name="title">
		<xsl:choose>
			<xsl:when test="/n1:ClinicalDocument/n1:title">
				<xsl:value-of select="/n1:ClinicalDocument/n1:title"/>
				<xsl:text> от </xsl:text>
				<xsl:call-template name="formatDate">
					<xsl:with-param name="date" select="/n1:ClinicalDocument/n1:effectiveTime/@value"/>
					<xsl:with-param name="presicion" select="8"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>Clinical Document</xsl:otherwise>
		</xsl:choose>
	</xsl:variable>
	<xsl:template match="/">
		<xsl:apply-templates select="n1:ClinicalDocument"/>
	</xsl:template>
	<xsl:template match="n1:ClinicalDocument">
		<html>
			<head>
				<title>
					<xsl:value-of select="$title"/>
				</title>
				<style type="text/css" media="screen">
        
			table.outer
            {
            width:100%;
            cellspacing:0;
            cellpadding:0;
            border: solid 2px #999999;
            border-collapse: collapse;  
            } 
            
            td.outer , th.outer 
            {
			font: 14px Arial, Helvetica, sans-serif;
            color: #003366;
            border: solid 2px #999999;
            }   
            
            th.outer
            {
            background: #EEEEEE;
			}
			
            td.outter
            {           
            background: #fff;
            }	
        
			table.Sections
            {
            border: none;
            border-collapse: collapse;  
            width: 100% ;
            margins: 0,0,0,0;
            padding: 0,0,0,0;
            cellspacing:0;
            }
            
            h2
            {
            font: bold 24px Arial, Helvetica, sans-serif;
            color: #333333;
            text-align: left; 
            }
            
            td.SectionTitle
            {
            border-top: solid 4px #999999;
            font-style: italic; 
            font-size: 12px;
            background: #EEEEEE;
            text-align:left;
            }
            
            td.SubSectionTitle
            {
            border:none;
            width: 16% ;
            font: bold 12px Arial, Helvetica, sans-serif;
            text-align: right;
            vertical-align : top ;
            padding-right: 5px;
            padding-top: 4px;
            }
            
            td.Rest
            {
			border-top: none;
			border-right: none;
			border-bottom: 1px solid #CCCCCC;
			border-left: none;
			font: 14px Arial, Helvetica, sans-serif;
			color: #003366;
			text-align: bottom; 
            }
            
			tr
			{
			vertical-align:top;
			}
            
            table.inner
            {
            cellspacing:1;
            cellpadding:5;
            border: solid 2px #999999;
            border-collapse: collapse;  
            } 
            
            table.inner th, table.inner td, table.inner tr
            {
			font: 14px Arial, Helvetica, sans-serif;
            color: #003366;
            border: solid 2px #999999;
            padding: 2px 5px; 
            }   
            
            th.inner
            {
            background: #EEEEEE;
			}
			
            td.inner
            {    
            background: #fff;
            }
					
        </style>
			</head>
			<body>
				<table class="outer" width="100%">
					<col width="40%"/>
					<col width="60%"/>					
					<tr class="outer">
						<th class="outer" width="20%">Направившее учреждение:</th>
						<td class="outer">
							<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:providerOrganization/n1:name"/>
							<xsl:text>"</xsl:text>
							<xsl:choose>
								<xsl:when test="not(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:providerOrganization/n1:addr[@nullFlavor])">
									<br/>
									<xsl:text>Адрес:</xsl:text>
									<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:providerOrganization/n1:addr"/>
								</xsl:when>
							</xsl:choose>
							<xsl:choose>
								<xsl:when test="not(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:providerOrganization/n1:telecom[@nullFlavor])">
									<br/>
									<xsl:text>Контакты:</xsl:text>
									<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:providerOrganization/n1:telecom"/>
								</xsl:when>
							</xsl:choose>
							<br/>
						</td>
					</tr>
					<!--					<tr class="outer">
						<th class="outer" width="20%">Описываемый интервал:</th>
						<td class="outer">
							<xsl:text>С </xsl:text>
							<xsl:call-template name="formatDate">
								<xsl:with-param name="date" select="/n1:ClinicalDocument/n1:componentOf/n1:encompassingEncounter/n1:effectiveTime/n1:low/@value"/>
								<xsl:with-param name="presicion" select="8"/>
							</xsl:call-template>
							<xsl:text> по </xsl:text>
							<xsl:call-template name="formatDate">
								<xsl:with-param name="date" select="/n1:ClinicalDocument/n1:componentOf/n1:encompassingEncounter/n1:effectiveTime/n1:high/@value"/>
								<xsl:with-param name="presicion" select="8"/>
							</xsl:call-template>
						</td>
					</tr>	-->
				</table>
				<br/>
				<br/>
				<br/>
				<h2>
					<xsl:value-of select="$title"/>
				</h2>
				<br/>
				<table class="outer" width="100%">
					<col width="40%"/>
					<col width="60%"/>							
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Пациент:</xsl:text>
						</th>
						<td class="outer">
							<b>
								<xsl:call-template name="getName">
									<xsl:with-param name="name" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:name"/>
								</xsl:call-template>
							</b>
						</td>
					</tr>				
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Идентификаторы пациента:</xsl:text>
						</th>
						<td class="outer">
							<xsl:call-template name="getId">
								<xsl:with-param name="Id" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:id[2]"/>
							</xsl:call-template>
							
							<strong><xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/f88:IdentityDoc/f88:IdentityCardTypeId/@displayName"/></strong>
							<xsl:text> </xsl:text>
							<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/f88:IdentityDoc/f88:Series"/>
							<xsl:text> </xsl:text>
							<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/f88:IdentityDoc/f88:Number"/>
							<xsl:text> </xsl:text>
							<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/f88:IdentityDoc/f88:IssueOrgName"/>
							<xsl:text> выдан:</xsl:text>
							<xsl:call-template name="formatDate">
								<xsl:with-param name="date" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/f88:IdentityDoc/f88:IssueDate/@value"/>
								<xsl:with-param name="presicion" select="8"/>
							</xsl:call-template>
							<br/>
							<xsl:if test="/n1:ClinicalDocument/n1:participant[@typeCode='HLD']/n1:associatedEntity/n1:id">
								<strong>Полис ОМС </strong>
								<xsl:value-of select="/n1:ClinicalDocument/n1:participant[@typeCode='HLD']/n1:associatedEntity/n1:id/@extension"/>
								<xsl:text> (</xsl:text>
								<xsl:value-of select="/n1:ClinicalDocument/n1:participant[@typeCode='HLD']/n1:associatedEntity/n1:scopingOrganization/n1:name"/>
								<xsl:text>)</xsl:text>
							</xsl:if>								
						</td>
					</tr>
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Пол:</xsl:text>
						</th>
						<td class="outer">
							<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:administrativeGenderCode/@displayName"/>
						</td>
					</tr>
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Дата рождения:</xsl:text>
						</th>
						<td class="outer">
							<xsl:value-of select="substring(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:birthTime/@value,7,2)"/>
							<xsl:text>.</xsl:text>
							<xsl:value-of select="substring(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:birthTime/@value,5,2)"/>
							<xsl:text>.</xsl:text>
							<xsl:value-of select="substring(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:birthTime/@value,1,4)"/>
							<xsl:text> (</xsl:text>
							<xsl:value-of select="substring(/n1:ClinicalDocument/n1:effectiveTime/@value,1,4) - substring(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:birthTime/@value,1,4)"/>
							<xsl:text> лет)</xsl:text>
						</td>
					</tr>
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Контактная информация:</xsl:text>
						</th>
						<td class="outer">
							<strong>Адрес регистрации:</strong>
							<br/>
							<xsl:choose>
								<xsl:when test="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:addr[@nullFlavor][@use='H'] or not(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:addr)">
									<xsl:text>Не указан</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:addr[@use='H']"/>
								</xsl:otherwise>
							</xsl:choose>
							<br/>
							<strong>Контакты:</strong>
							<br/>
							<xsl:choose>
								<xsl:when test="not(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:telecom[@nullFlavor])">
									<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:telecom"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>Отсутствуют</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
							<br/>
						</td>
					</tr>
					<xsl:if test="/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component/n1:section/n1:text/n1:list[@ID='priveleges']">
						<tr class="outer">
							<th class="outer" width="20%">
								<xsl:value-of select="/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component/n1:section/n1:text/n1:list[@ID='priveleges']/n1:caption"/><xsl:text>:</xsl:text>
							</th>
							<td class="outer">
								<xsl:for-each select="/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component/n1:section/n1:text/n1:list[@ID='priveleges']/n1:item">
									<xsl:copy-of select="."/>
									<br/>
								</xsl:for-each>
							</td>
						</tr>
					</xsl:if>	
					<xsl:if test="/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component/n1:section/n1:text/n1:list[@ID='invalid']">
						<tr class="outer">
							<th class="outer" width="20%">
								<xsl:value-of select="/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component/n1:section/n1:text/n1:list[@ID='invalid']/n1:caption"/><xsl:text>:</xsl:text>
							</th>
							<td class="outer">
								<xsl:for-each select="/n1:ClinicalDocument/n1:component/n1:structuredBody/n1:component/n1:section/n1:text/n1:list[@ID='invalid']/n1:item">
									<xsl:copy-of select="."/>
									<br/>
								</xsl:for-each>
							</td>
						</tr>
					</xsl:if>
				</table>
				<br/>
								
				<xsl:choose>
					<xsl:when test="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianPerson">
						<table class="outer" width="100%">
							<col width="40%"/>
							<col width="60%"/>							
							<tr class="outer">
								<th class="outer" width="20%">
									<xsl:text>Законный представитель:</xsl:text>
								</th>
								<td class="outer">
									<b>
										<xsl:call-template name="getName">
											<xsl:with-param name="name" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianPerson/n1:name"/>
										</xsl:call-template>
									</b>
								</td>
							</tr>
							<tr class="outer">
								<th class="outer" width="20%">
									<xsl:text>Идентификаторы законного представителя:</xsl:text>
								</th>
								<td class="outer">
									<xsl:call-template name="getId">
										<xsl:with-param name="Id" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:id[1]"/>
									</xsl:call-template>
									
									<strong><xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:IdentityDoc/f88:IdentityCardTypeId/@displayName"/></strong>
									<xsl:text> </xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:IdentityDoc/f88:Series"/>
									<xsl:text> </xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:IdentityDoc/f88:Number"/>
									<xsl:text> </xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:IdentityDoc/f88:IssueOrgName"/>
									<xsl:text> выдан:</xsl:text>
									<xsl:call-template name="formatDate">
										<xsl:with-param name="date" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:IdentityDoc/f88:IssueDate/@value"/>
										<xsl:with-param name="presicion" select="8"/>
									</xsl:call-template>
									<br/>
									
									<strong><xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:AuthorityDoc/f88:IdentityCardTypeId/@displayName"/></strong>
									<xsl:text> </xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:AuthorityDoc/f88:IdentityDoc/f88:Series"/>
									<xsl:text> </xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:AuthorityDoc/f88:IdentityDoc/f88:Number"/>
									<xsl:text> </xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:AuthorityDoc/f88:IssueOrgName"/>
									<xsl:text> выдан:</xsl:text>
									<xsl:call-template name="formatDate">
										<xsl:with-param name="date" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/f88:AuthorityDoc/f88:IssueDate/@value"/>
										<xsl:with-param name="presicion" select="8"/>
									</xsl:call-template>
									<br/>
								</td>
							</tr>
							<tr class="outer">
								<th class="outer" width="20%">
									<xsl:text>Контактная информация:</xsl:text>
								</th>
								<td class="outer">
									<strong>Адрес:</strong>
									<br/>
									<xsl:choose>
										<xsl:when test="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:addr[@nullFlavor]">
											<xsl:text>Не указан</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:addr"/>
										</xsl:otherwise>
									</xsl:choose>
									<br/>
									<strong>Контакты:</strong>
									<br/>
									<xsl:choose>
										<xsl:when test="not(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:telecom[@nullFlavor])">
											<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:telecom"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:text>Отсутствуют</xsl:text>
										</xsl:otherwise>
									</xsl:choose>
									<br/>
								</td>
							</tr>					
						</table>
					</xsl:when>
					<xsl:when test="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianOrganization">
						<table class="outer" width="100%">
							<col width="40%"/>
							<col width="60%"/>							
							<tr class="outer">
								<th class="outer" width="20%">
									<xsl:text>Попечительство юридическим лицом:</xsl:text>
								</th>
								<td class="outer">
									<xsl:call-template name="getName">
										<xsl:with-param name="name" select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianOrganization/n1:name"/>
									</xsl:call-template>
									<xsl:text>, ОГРН:</xsl:text>
									<xsl:value-of select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianOrganization/f88:Ogrn"/>							
									<xsl:choose>
										<xsl:when test="not(/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianOrganization/n1:addr[@nullFlavor])">
											<br/>
											<xsl:text>Адрес:</xsl:text>
											<xsl:apply-templates select="/n1:ClinicalDocument/n1:recordTarget/n1:patientRole/n1:patient/n1:guardian/n1:guardianOrganization/n1:addr"/>
										</xsl:when>
									</xsl:choose>
									<br/>
								</td>
							</tr>					
						</table>						
					</xsl:when>
				</xsl:choose>
				
				<br/>
				<table class="outer" width="100%">
					<col width="40%"/>
					<col width="60%"/>							
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Гражданин находится:</xsl:text>
						</th>
						<td class="outer">
							<xsl:value-of select="/n1:ClinicalDocument/n1:participant[@typeCode='LOC']/n1:associatedEntity/n1:code/@displayName"/>
							<xsl:text>, ОГРН:</xsl:text>
							<xsl:value-of select="/n1:ClinicalDocument/n1:participant[@typeCode='LOC']/n1:associatedEntity/n1:scopingOrganization/f88:Ogrn"/>							
							<br/>
							
							<xsl:choose>
								<xsl:when test="/n1:ClinicalDocument/n1:participant[@typeCode='LOC']/n1:associatedEntity/n1:scopingOrganization/n1:addr">
									<xsl:text>Адрес:</xsl:text>							
									<xsl:apply-templates select="/n1:ClinicalDocument/n1:participant[@typeCode='LOC']/n1:associatedEntity/n1:scopingOrganization/n1:addr"/>
								</xsl:when>
							</xsl:choose>
						</td>
					</tr>	
				</table>
				
				<br/>
				<xsl:apply-templates select="n1:component/n1:structuredBody|n1:component/n1:nonXMLBody"/>
				<br/>
				<br/>
				<table class="outer" width="100%">
					<col width="40%"/>
					<col width="60%"/>	
					<xsl:if test="/n1:ClinicalDocument/n1:documentationOf/n1:serviceEvent/n1:performer[@typeCode='PPRF']">
						<tr class="outer">
							<th class="outer" width="20%">
								<xsl:text>Председатель врачебной комиссии:</xsl:text>
							</th>
							<td class="outer">
								<xsl:call-template name="translateCode">
									<xsl:with-param name="code" select="/n1:ClinicalDocument/n1:documentationOf/n1:serviceEvent/n1:performer[@typeCode='PPRF']/n1:assignedEntity/n1:code"/>
								</xsl:call-template>
								<xsl:text> </xsl:text>
								<xsl:call-template name="getName">
									<xsl:with-param name="name" select="/n1:ClinicalDocument/n1:documentationOf/n1:serviceEvent/n1:performer[@typeCode='PPRF']/n1:assignedEntity/n1:assignedPerson/n1:name"/>
								</xsl:call-template>
							</td>
						</tr>
					</xsl:if>					
					<xsl:if test="/n1:ClinicalDocument/n1:documentationOf/n1:serviceEvent/n1:performer[@typeCode='SPRF']">
						<xsl:for-each select="/n1:ClinicalDocument/n1:documentationOf/n1:serviceEvent/n1:performer[@typeCode='SPRF']">
							<tr class="outer">
								<th class="outer" width="20%">
									<xsl:text>Член врачебной комиссии:</xsl:text>
								</th>
								<td class="outer">
									<xsl:call-template name="translateCode">
										<xsl:with-param name="code" select="n1:assignedEntity/n1:code"/>
									</xsl:call-template>
									<xsl:text> </xsl:text>
									<xsl:call-template name="getName">
										<xsl:with-param name="name" select="n1:assignedEntity/n1:assignedPerson/n1:name"/>
									</xsl:call-template>
								</td>
							</tr>
						</xsl:for-each>	
					</xsl:if>
				</table>
				<br/>
				<br/>
				<table class="outer" width="100%">
					<col width="40%"/>
					<col width="60%"/>							
					<xsl:if test="/n1:ClinicalDocument/n1:author/n1:assignedAuthor">
						<tr class="outer">
							<th class="outer" width="20%">
								<xsl:text>Документ составил:</xsl:text>
							</th>
							<td class="outer">
								<xsl:call-template name="translateCode">
									<xsl:with-param name="code" select="/n1:ClinicalDocument/n1:author/n1:assignedAuthor/n1:code"/>
								</xsl:call-template>
								<xsl:text> </xsl:text>
								<xsl:call-template name="getName">
									<xsl:with-param name="name" select="/n1:ClinicalDocument/n1:author/n1:assignedAuthor/n1:assignedPerson/n1:name"/>
								</xsl:call-template>
							</td>
						</tr>
					</xsl:if>
					<xsl:if test="null">
						<tr class="outer">
							<th class="outer" width="20%">
								<xsl:text>Документ проверил:</xsl:text>
							</th>
							<td class="outer">
							</td>
						</tr>
					</xsl:if>
					<tr class="outer">
						<th class="outer" width="20%">
							<xsl:text>Документ заверил:</xsl:text>
						</th>
						<td class="outer">
							<xsl:call-template name="translateCode">
								<xsl:with-param name="code" select="/n1:ClinicalDocument/n1:legalAuthenticator/n1:assignedEntity/n1:code"/>
							</xsl:call-template>
							<xsl:text> </xsl:text>
							<xsl:call-template name="getName">
								<xsl:with-param name="name" select="/n1:ClinicalDocument/n1:legalAuthenticator/n1:assignedEntity/n1:assignedPerson/n1:name"/>
							</xsl:call-template>
						</td>
					</tr>
				</table>
			</body>
		</html>
	</xsl:template>
	<!-- Get a Name  -->
	<xsl:template name="getName">
		<xsl:param name="name"/>
		<xsl:choose>
			<xsl:when test="$name/n1:family">
				<xsl:if test="$name/n1:prefix">
					<xsl:value-of select="$name/n1:prefix"/>
					<xsl:text> </xsl:text>
				</xsl:if>
				<xsl:value-of select="$name/n1:family"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="$name/n1:given[1]"/>
				<xsl:text> </xsl:text>
				<xsl:value-of select="$name/n1:given[2]"/>
				<xsl:text> </xsl:text>
				<xsl:if test="$name/n1:suffix">
					<xsl:value-of select="$name/n1:suffix"/>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$name"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- Get a Job  -->
	<xsl:template name="getJob">
		<xsl:param name="assignedEntity"/>
		<xsl:choose>
			<xsl:when test="$assignedEntity/n1:code">
				<xsl:value-of select="$assignedEntity/n1:code/@displayName"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$assignedEntity/n1:code/@code"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!--  Format Date 
    outputs a date in Day Month Year MM:SS form
    -->
	<xsl:template name="formatDate">
		<xsl:param name="date"/>
		<xsl:param name="presicion"/>
		<xsl:variable name="dateLength" select="string-length(substring($date,1,$presicion))"/>
		<xsl:if test="$dateLength>=8">
			<xsl:choose>
				<xsl:when test="substring ($date, 7, 1)='0'">
					<xsl:value-of select="substring ($date, 8, 1)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="substring ($date, 7, 2)"/>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:text> </xsl:text>
		</xsl:if>
		<xsl:if test="$dateLength>=6">
			<xsl:variable name="month" select="substring ($date, 5, 2)"/>
			<xsl:choose>
				<xsl:when test="$month='01'">
					<xsl:text>Января </xsl:text>
				</xsl:when>
				<xsl:when test="$month='02'">
					<xsl:text>Февраля </xsl:text>
				</xsl:when>
				<xsl:when test="$month='03'">
					<xsl:text>Марта</xsl:text>
				</xsl:when>
				<xsl:when test="$month='04'">
					<xsl:text>Апреля </xsl:text>
				</xsl:when>
				<xsl:when test="$month='05'">
					<xsl:text>Мая</xsl:text>
				</xsl:when>
				<xsl:when test="$month='06'">
					<xsl:text>Июня</xsl:text>
				</xsl:when>
				<xsl:when test="$month='07'">
					<xsl:text>Июля </xsl:text>
				</xsl:when>
				<xsl:when test="$month='08'">
					<xsl:text>Августа </xsl:text>
				</xsl:when>
				<xsl:when test="$month='09'">
					<xsl:text>Сентября </xsl:text>
				</xsl:when>
				<xsl:when test="$month='10'">
					<xsl:text>Октября </xsl:text>
				</xsl:when>
				<xsl:when test="$month='11'">
					<xsl:text>Ноября </xsl:text>
				</xsl:when>
				<xsl:when test="$month='12'">
					<xsl:text>Декабря </xsl:text>
				</xsl:when>
			</xsl:choose>
			<xsl:text> </xsl:text>
		</xsl:if>
		<xsl:value-of select="substring ($date, 1, 4)"/>
		<xsl:if test="$dateLength>=12">
			<xsl:text> </xsl:text>
			<xsl:choose>
				<xsl:when test="substring ($date, 9, 1)='0'">
					<xsl:value-of select="substring ($date, 10, 1)"/>
					<xsl:text>:</xsl:text>
					<xsl:value-of select="substring ($date, 11, 2)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="substring ($date, 9, 2)"/>
					<xsl:text>:</xsl:text>
					<xsl:value-of select="substring ($date, 11, 2)"/>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:text> </xsl:text>
			<xsl:value-of select="substring ($date, 13, 5)"/>
		</xsl:if>
	</xsl:template>
	<xsl:template match="n1:component/n1:nonXMLBody">
		<xsl:choose>
			<!-- if there is a reference, use that in an IFRAME -->
			<xsl:when test="n1:text/n1:reference">
				<IFRAME name="nonXMLBody" id="nonXMLBody" WIDTH="100%" HEIGHT="66%" src="{n1:text/n1:reference/@value}"/>
			</xsl:when>
			<xsl:when test="n1:text/@mediaType='text/plain'">
				<pre>
					<xsl:value-of select="n1:text/text()"/>
				</pre>
			</xsl:when>
			<xsl:otherwise>
				<CENTER>Cannot display the text</CENTER>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- StructuredBody-->
	<xsl:template match="n1:component/n1:structuredBody">
		<table class="Sections">
			<tbody>
				<xsl:for-each select="n1:component/n1:section[n1:code/@code!='SERVICES']">
					<tr>
						<td colspan="2">
							<br/>
						</td>
					</tr>
					<tr>
						<td class="SectionTitle" colspan="2">
							<xsl:apply-templates select="n1:title">
								<xsl:with-param name="code" select="n1:code/@code"/>
							</xsl:apply-templates>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<br/>
						</td>
					</tr>
					<tr>
						<td class="SubSectionTitle">
						
						</td>
						<td class="Rest">
							<xsl:apply-templates select="n1:text"/>
						</td>
					</tr>
				    <xsl:for-each select="n1:component/n1:section[n1:code/@code!='SERVICES']">
						<tr><td><br/></td></tr>
						<tr>
							<td class="SubSectionTitle">
								<xsl:apply-templates select="n1:title">
									<xsl:with-param name="code" select="n1:code/@code"/>
								</xsl:apply-templates>
							</td>
							<td class="Rest">
								<xsl:apply-templates select="n1:text"/>
							    <xsl:for-each select="n1:component/n1:section[n1:code/@code!='SERVICES']">
									<xsl:variable name="code" select="n1:code/@code='8716-3'"/>
									<br/>
									<xsl:choose>
										<xsl:when test="$code='8716-3'">
											<br/>
											<xsl:apply-templates select="n1:title">
												<xsl:with-param name="code" select="n1:code/@code"/>
											</xsl:apply-templates>
											<xsl:text> </xsl:text>
											<xsl:apply-templates select="n1:text"/>
											<xsl:text> </xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<br/>
											<xsl:apply-templates select="n1:title">
												<xsl:with-param name="code" select="n1:code/@code"/>
											</xsl:apply-templates>
											<br/>
											<xsl:text> </xsl:text>
											<xsl:apply-templates select="n1:text"/>
											<xsl:for-each select="n1:component/n1:section">
												<xsl:apply-templates select="n1:title">
													<xsl:with-param name="code" select="n1:code/@code"/>
												</xsl:apply-templates>
												<br/>
												<xsl:text> </xsl:text>
												<xsl:apply-templates select="n1:text"/>
												<xsl:text> </xsl:text>
												<br/>
											</xsl:for-each>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:for-each>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<br/>
							</td>
						</tr>
					</xsl:for-each>
				</xsl:for-each>
			</tbody>
		</table>
	</xsl:template>
	<!--   Title  -->
	<xsl:template match="n1:title">
		<xsl:param name="code" select="''"/>
		<span style="font-weight:bold;" title="{$code}">
			<xsl:value-of select="."/>
		</span>
	</xsl:template>
	<!--   Text   -->
	<xsl:template match="n1:text">
		<xsl:apply-templates/>
	</xsl:template>
	<!--   paragraph  -->
	<xsl:template match="n1:paragraph">
		<p>
		<xsl:apply-templates/>
		</p>
	</xsl:template>
	<!-- caption -->
	<xsl:template match="n1:caption">
			<span style="font-weight:bold;">
				<xsl:value-of select="."/>
				<xsl:text>: </xsl:text>
			</span>
		<br/>
	</xsl:template>
	<!--   line break  -->
	<xsl:template match="n1:br">
		<xsl:apply-templates/>
		<br/>
	</xsl:template>
	<!--     Content w/ deleted text is hidden -->
	<xsl:template match="n1:content[@revised='delete']"/>
	<!--   content  -->
	<xsl:template match="n1:content">
		<xsl:apply-templates/>
	</xsl:template>
	<!--   list  -->
	<xsl:template match="n1:list">
		<xsl:choose>
			<xsl:when test="(@ID='invalid')or(@ID='priveleges')">
			</xsl:when>
			<xsl:otherwise>
				<!-- caption -->
				<xsl:if test="n1:caption">
					<span style="font-weight:bold; ">
						<xsl:apply-templates select="n1:caption"/>
					</span>
				</xsl:if>
				<!-- item -->
				<xsl:choose>
					<xsl:when test="@listType='ordered'">
						<ol>
							<xsl:for-each select="n1:item">
								<li>
									<!-- list element-->
									<xsl:apply-templates/>
								</li>
							</xsl:for-each>
						</ol>
					</xsl:when>
					<xsl:otherwise>
						<!-- list is unordered -->
						<ul>
							<xsl:for-each select="n1:item">
								<li>
									<!-- list element-->
									<xsl:apply-templates/>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>	
	</xsl:template>
	<!-- tables -->
	<xsl:template match="n1:table/@*|n1:thead/@*|n1:tfoot/@*|n1:tbody/@*|n1:colgroup/@*|n1:col/@*|n1:tr/@*|n1:th/@*|n1:td/@*">
		<xsl:copy>
			<xsl:apply-templates select="."/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="n1:table">		
		<xsl:element name="table">
			<xsl:attribute name="class">inner</xsl:attribute>		
			<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>				
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="n1:thead">
		<thead>
			<xsl:apply-templates/>
		</thead>
	</xsl:template>
	<xsl:template match="n1:tfoot">
		<tfoot>
			<xsl:apply-templates/>
		</tfoot>
	</xsl:template>
	<xsl:template match="n1:tbody">
		<tbody>
			<xsl:apply-templates/>
		</tbody>
	</xsl:template>
	<xsl:template match="n1:colgroup">
		<colgroup>
			<xsl:apply-templates/>
		</colgroup>
	</xsl:template>
	<xsl:template match="n1:col">
		<xsl:copy-of select="."/>
	</xsl:template>
	<xsl:template match="n1:tr">
		<tr>
			<xsl:apply-templates/>
		</tr>
	</xsl:template>
	<xsl:template match="n1:th">
		<th class="inner">
			<xsl:apply-templates/>
		</th>
	</xsl:template>
	<xsl:template match="n1:td">
		<xsl:element name="td">
			<xsl:attribute name="class">inner</xsl:attribute>
			<xsl:attribute name="colspan"><xsl:value-of select="@colspan"/></xsl:attribute>	
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="n1:table/n1:caption">
		<span style="font-weight:bold; ">
			<xsl:apply-templates/>
		</span>
	</xsl:template>
	<!--   RenderMultiMedia 
    
    this currently only handles GIF's and JPEG's.  It could, however,
    be extended by including other image MIME types in the predicate
    and/or by generating <object> or <applet> tag with the correct
    params depending on the media type  @ID  =$imageRef  referencedObject
    -->
	<xsl:template match="n1:renderMultiMedia">
		<xsl:variable name="imageRef" select="@referencedObject"/>
		<xsl:choose>
			<xsl:when test="//n1:regionOfInterest[@ID=$imageRef]">
				<!-- Here is where the Region of Interest image referencing goes -->
				<xsl:if test="//n1:regionOfInterest[@ID=$imageRef]//n1:observationMedia/n1:value[@mediaType='image/gif'           or
          @mediaType='image/jpeg']">
					<br clear="all"/>
					<xsl:element name="img">
						<xsl:attribute name="src"><xsl:value-of select="//n1:regionOfInterest[@ID=$imageRef]//n1:observationMedia/n1:value/n1:reference/@value"/></xsl:attribute>
					</xsl:element>
				</xsl:if>
			</xsl:when>
			<xsl:otherwise>
				<!-- Here is where the direct MultiMedia image referencing goes -->
				<xsl:if test="//n1:observationMedia[@ID=$imageRef]/n1:value[@mediaType='image/gif' or           @mediaType='image/jpeg']">
					<br clear="all"/>
					<xsl:element name="img">
						<xsl:attribute name="src"><xsl:value-of select="//n1:observationMedia[@ID=$imageRef]/n1:value/n1:reference/@value"/></xsl:attribute>
					</xsl:element>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!--    Stylecode processing   
    Supports Bold, Underline and Italics display
    -->
	<xsl:template match="//n1:*[@styleCode]">
		<xsl:if test="@styleCode='Bold'">
			<xsl:element name="b">
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:if>
		<xsl:if test="@styleCode='Italics'">
			<xsl:element name="i">
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:if>
		<xsl:if test="@styleCode='Underline'">
			<xsl:element name="u">
				<xsl:apply-templates/>
			</xsl:element>
		</xsl:if>
		<xsl:if test="contains(@styleCode,'Bold') and contains(@styleCode,'Italics') and not (contains(@styleCode, 'Underline'))">
			<xsl:element name="b">
				<xsl:element name="i">
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:element>
		</xsl:if>
		<xsl:if test="contains(@styleCode,'Bold') and contains(@styleCode,'Underline') and not (contains(@styleCode, 'Italics'))">
			<xsl:element name="b">
				<xsl:element name="u">
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:element>
		</xsl:if>
		<xsl:if test="contains(@styleCode,'Italics') and contains(@styleCode,'Underline') and not (contains(@styleCode, 'Bold'))">
			<xsl:element name="i">
				<xsl:element name="u">
					<xsl:apply-templates/>
				</xsl:element>
			</xsl:element>
		</xsl:if>
		<xsl:if test="contains(@styleCode,'Italics') and contains(@styleCode,'Underline') and contains(@styleCode, 'Bold')">
			<xsl:element name="b">
				<xsl:element name="i">
					<xsl:element name="u">
						<xsl:apply-templates/>
					</xsl:element>
				</xsl:element>
			</xsl:element>
		</xsl:if>
		<xsl:if test="not (contains(@styleCode,'Italics') or contains(@styleCode,'Underline') or contains(@styleCode, 'Bold'))">
			<xsl:apply-templates/>
		</xsl:if>
	</xsl:template>
	<!--    Superscript or Subscript   -->
	<xsl:template match="n1:sup">
		<xsl:element name="sup">
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="n1:sub">
		<xsl:element name="sub">
			<xsl:apply-templates/>
		</xsl:element>
	</xsl:template>
	<!-- 
    Contact Information
  -->
	<xsl:template name="getContactInfo">
		<xsl:param name="contact"/>
		<xsl:apply-templates select="$contact/n1:addr"/>
		<xsl:text>, </xsl:text>
		<xsl:apply-templates select="$contact/n1:telecom"/>
	</xsl:template>
	<xsl:template match="n1:addr">
		<xsl:choose>
			<xsl:when test="count(./*)>0">
				<xsl:for-each select="n1:streetAddressLine">
					<xsl:text> </xsl:text>
					<xsl:value-of select="."/>
				</xsl:for-each>
				<!--<xsl:if test="n1:state">
					<xsl:text> Регион </xsl:text>
					<xsl:value-of select="n1:state"/>
				</xsl:if>-->
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="."/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="n1:telecom">
		<xsl:variable name="type" select="substring-before(@value, ':')"/>
		<xsl:variable name="value" select="substring-after(@value, ':')"/>
		<xsl:choose>
			<xsl:when test="$type='tel'">
				<xsl:text> Тел.</xsl:text>
			</xsl:when>
			<xsl:when test="$type='fax'">
				<xsl:text> Факс</xsl:text>
			</xsl:when>
			<xsl:when test="$type='mailto'">
				<xsl:text> Электронная почта</xsl:text>
			</xsl:when>
			<xsl:when test="$type='http'">
				<xsl:text> Сайт</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:text> </xsl:text>
				<xsl:value-of select="$type"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:if test="@use">
			<xsl:choose>
				<xsl:when test="@use='HP'">
					<xsl:text>(дом.)</xsl:text>
				</xsl:when>
				<xsl:when test="@use='WP'">
					<xsl:text>(раб.)</xsl:text>
				</xsl:when>
				<xsl:when test="@use='MC'">
					<xsl:text>(моб.)</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>(</xsl:text>
					<xsl:value-of select="@use"/>
					<xsl:text>)</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
		<xsl:text>: </xsl:text>
		<xsl:value-of select="$value"/>
		<xsl:text>;</xsl:text>
	</xsl:template>
	<!-- 
  -->
	<xsl:template name="payer">
		<xsl:for-each select="/n1:ClinicalDocument/n1:participant[@typeCode='HLD']">

            Полис:<xsl:value-of select="n1:associatedEntity/n1:id/@extension"/>
			<br/>
			Страховая компания: 
				<xsl:call-template name="getName">
				<xsl:with-param name="name" select="n1:associatedEntity/n1:scopingOrganization/n1:name"/>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	<!-- 
  -->
	<xsl:template name="support">
		<table width="100%">
			<col width="60%"/>
			<col width="40%"/>
			<xsl:for-each select="/n1:ClinicalDocument/n1:participant[@typeCode='IND']">
				<tr>
					<td>
						<xsl:call-template name="getName">
							<xsl:with-param name="name" select="n1:associatedEntity/n1:associatedPerson/n1:name"/>
						</xsl:call-template>
					</td>
					<td>
						<xsl:call-template name="getContactInfo">
							<xsl:with-param name="contact" select="n1:associatedEntity"/>
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
	<!-- 
  -->
	<xsl:template name="performer">
		<table width="100%">
			<col width="60%"/>
			<col width="40%"/>
			<xsl:for-each select="//n1:serviceEvent/n1:performer">
				<tr>
					<td>
						<xsl:call-template name="getName">
							<xsl:with-param name="name" select="n1:assignedEntity/n1:assignedPerson/n1:name"/>
						</xsl:call-template>
					</td>
					<td>
						<xsl:call-template name="getContactInfo">
							<xsl:with-param name="contact" select="n1:assignedEntity"/>
						</xsl:call-template>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
	<!-- 
  -->
	<!--  Bottomline  -->
	<xsl:template name="bottomline">
		<table class="outer" width="100%">
			<xsl:for-each select="/n1:ClinicalDocument/n1:informant">
				<tr class="outer">
					<td class="outer" width="20%">
						<b>
							<xsl:text>Информант:</xsl:text>
						</b>
					</td>
					<td class="outer">
						<table>
							<col width="60%"/>
							<col width="40%"/>
							<tr>
								<td>
									<xsl:if test="n1:assignedEntity/n1:assignedPerson|n1:relatedEntity/n1:relatedPerson">
										<xsl:call-template name="getName">
											<xsl:with-param name="name" select="n1:assignedEntity/n1:assignedPerson/n1:name|n1:relatedEntity/n1:relatedPerson/n1:name"/>
										</xsl:call-template>
										<xsl:if test="n1:relatedEntity/n1:code">
											<xsl:text> (</xsl:text>
											<xsl:call-template name="translateCode">
												<xsl:with-param name="code" select="n1:relatedEntity/n1:code/@code"/>
											</xsl:call-template>
											<xsl:text>)</xsl:text>
										</xsl:if>
									</xsl:if>
								</td>
								<td>
									<xsl:call-template name="getContactInfo">
										<xsl:with-param name="contact" select="n1:assignedEntity|n1:relatedEntity"/>
									</xsl:call-template>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
	<!-- 
  -->
	<xsl:template name='translateCode'>
		<xsl:param name='code'/>
		<xsl:choose>
			<xsl:when test="$code/@displayName">
				<xsl:value-of select="$code/@displayName"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$code/@code"/>
				<xsl:text> (</xsl:text>
				<xsl:value-of select="$code/@codeSystem"/>
				<xsl:text>)</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template name='getId'>
		<xsl:param name='Id'/>
		<xsl:choose>
			<xsl:when test="$Id/@root='1.2.643.100.3'">
				<strong>СНИЛС </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.1'">
				<strong>Паспорт гражданина РФ </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.2'">
				<strong>Заграничный паспорт гражданина Российской Федерации  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.3'">
				<strong>Дипломатический паспорт  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.4'">
				<strong>Служебный паспорт </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.5'">
				<strong>Временное удостоверение личности гражданина Российской Федерации (форма № 2П) </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.6'">
				<strong>Свидетельство о рождении  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.7'">
				<strong>Удостоверение личности моряка  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.8'">
				<strong>Удостоверение личности военнослужащего Российской Федерации  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.9'">
				<strong>Военный билет (у лиц, проходящих военную службу)  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.10'">
				<strong>Паспорт иностранного гражданина или иной документ, удостоверяющий личность иностранного гражданина </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.11'">
				<strong>Вид на жительство иностранного гражданина  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.12'">
				<strong>Удостоверение беженца  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.13'">
				<strong>Свидетельство о предоставлении временного убежища на территории Российской Федерации  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.14'">
				<strong>Свидетельство о рассмотрении ходатайства о признании лица беженцем на территории Российской Федерации по существу  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.15'">
				<strong>Проездной документ беженца  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.16'">
				<strong>Вид на жительство, выдаваемое лицу без гражданства  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.17'">
				<strong>Разрешение на временное проживание, выдаваемое лицу без гражданства </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.18'">
				<strong>Документ, удостоверяющий личность лица без гражданства, выданный иностранным государством </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.48.19'">
				<strong>Иные документы, предусмотренные федеральным законом или признаваемые в соответствии с международным договором Российской Федерации в качестве документов, удостоверяющих личность лица без гражданства </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>	
			
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.313.1'">
				<strong>Свидетельство о рождении  </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.313.2'">
				<strong>Решение о назначении лица опекуном </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.313.3'">
				<strong>Решение о назначении лица попечителем </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>
			<xsl:when test="$Id/@root='1.2.643.5.1.13.13.99.2.313.4'">
				<strong>Доверенность </strong>
				<xsl:value-of select="$Id/@extension"/>
			</xsl:when>	
							
			<xsl:otherwise>
				<xsl:value-of select="$Id/@extension"/>
				<xsl:text> (</xsl:text>
				<xsl:value-of select="$Id/@root"/>
				<xsl:text>)</xsl:text>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:choose>
			<xsl:when test="$Id/@assigningAuthorityName">
				<xsl:text> (</xsl:text>
				<xsl:value-of select="$Id/@assigningAuthorityName"/>
				<xsl:text>)</xsl:text>
			</xsl:when>
		</xsl:choose>		
				
		<br/>
	</xsl:template>
</xsl:stylesheet>
