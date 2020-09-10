<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version = '1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform' xmlns:xsd='http://www.w3.org/2001/XMLSchema'>
	<xsl:template match="/">
			 <data>
				<xtype>fieldset</xtype>
				<autoHeight>true</autoHeight>
				<region>center</region>
				<items>					
					<arrayNode>
						<defaults><style>padding: 5px;</style></defaults>
						<autoHeight>true</autoHeight>
						<bodyStyle>padding: 5px; border: 0; text-align: right</bodyStyle>						
						<items>
							<arrayNode>
								<xtype>label</xtype>
								<text>Дата: <xsl:value-of select="//xsd:element[@name='DocumentDate']/@default"/> </text>
							</arrayNode>
							<arrayNode>
								<xtype>label</xtype>
								<text> Время: <xsl:value-of select="//xsd:element[@name='DocumentTime']/@default"/> </text>
							</arrayNode>
							<arrayNode>
								<xtype>label</xtype>
								<text> Пациент: <xsl:value-of select="//xsd:element[@name='Person_FIO']/@default"/> </text>
							</arrayNode>
						</items>
					</arrayNode>
					<arrayNode>
						<defaults><style>padding: 5px;</style></defaults>
						<autoHeight>true</autoHeight>
						<bodyStyle>padding: 5px; border: 0; text-align: center; font-weight: bold; font-size: 14pt</bodyStyle>						
						<items>
							<arrayNode>
								<xtype>label</xtype>
								<text>Запись хирурга при первоначальном осмотре</text>
							</arrayNode>
						</items>
					</arrayNode>
					<arrayNode>
						<style>border: 0;</style>
						<autoHeight>true</autoHeight>
						<labelAlign>top</labelAlign>
						<xtype>fieldset</xtype>
						<defaults><labelStyle>font-weight:bold;</labelStyle></defaults>
						<items>
							<arrayNode>
								<fieldLabel>Жалобы</fieldLabel>
								<xtype>textarea</xtype>
								<name>Jaloby</name>
								<width>600</width>
								<value><xsl:value-of select="//Jaloby"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>Анамнез заболевания</fieldLabel>
								<xtype>textarea</xtype>
								<name>Anamnes_Zab</name>
								<width>600</width>
								<value><xsl:value-of select="//Anamnes_Zab"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>Анамнез жизни</fieldLabel>
								<xtype>textarea</xtype>
								<id>id_Anamnez_Jizn</id>
								<name>Anamnez_Jizn</name>
								<width>600</width>
								<value><xsl:value-of select="//Anamnez_Jizn"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>Экспертный анамнез</fieldLabel>
								<xtype>textarea</xtype>
								<name>Expert_Anamn</name>
								<width>600</width>
								<value><xsl:value-of select="//Expert_Anamn"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>Объективный статус</fieldLabel>
								<xtype>textarea</xtype>
								<name>Object_Stat</name>
								<width>600</width>
								<value><xsl:value-of select="//Object_Stat"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>Локальный статус</fieldLabel>
								<xtype>textarea</xtype>
								<name>Local_Stat</name>
								<width>600</width>
								<value><xsl:value-of select="//Local_Stat"/></value>
							</arrayNode>
						</items>
					</arrayNode>					
					<arrayNode>
						<defaults><style>padding: 5px;</style></defaults>
						<autoHeight>true</autoHeight>
						<bodyStyle>padding: 5px; border: 0; text-align: right</bodyStyle>						
						<items>
							<arrayNode>
								<xtype>label</xtype>
								<text>Врач: </text>
							</arrayNode>
							<arrayNode>
								<xtype>label</xtype>
								<text><xsl:value-of select="//xsd:element[@name='MedPersonal_FIO']/@default"/></text>
							</arrayNode>									
						</items>
					</arrayNode>
				</items>
			</data>
	</xsl:template>
</xsl:stylesheet>