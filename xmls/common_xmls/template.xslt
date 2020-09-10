<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version = '1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
	<xsl:template match="/">
			 <data>
				<xtype>fieldset</xtype>
				<autoHeight>true</autoHeight>
				<region>center</region>
				<style>border: 0;</style>				
				<items>
					<arrayNode>
						<fieldLabel>Объективные данные</fieldLabel>
						<xtype>textarea</xtype>
						<name>ObjectiveData</name>
						<width>600</width>
						<value><xsl:value-of select="//ObjectiveData"/></value>
					</arrayNode>
					<arrayNode>
						<fieldLabel>Обследование</fieldLabel>
						<xtype>textarea</xtype>
						<name>Examination</name>
						<width>600</width>
						<value><xsl:value-of select="//Examination"/></value>
					</arrayNode>
					<arrayNode>
						<fieldLabel>Назначенное лечение</fieldLabel>
						<xtype>textarea</xtype>
						<name>AssignedCure</name>
						<width>600</width>
						<value><xsl:value-of select="//AssignedCure"/></value>
					</arrayNode>
					<arrayNode>
						<fieldLabel>Рекомендации</fieldLabel>
						<xtype>textarea</xtype>
						<name>Recomendations</name>
						<width>600</width>
						<value><xsl:value-of select="//Recomendations"/></value>
					</arrayNode>
				</items>
			</data>
	</xsl:template>
</xsl:stylesheet>