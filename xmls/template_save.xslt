<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version = '1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
	<xsl:template match="/">
			 <data>
				<xtype>fieldset</xtype>
				<title>Контейнер</title>
				<autoHeight>1</autoHeight>
				<layout>form</layout>
				<items>
					<arrayNode>
						<fieldLabel>Код</fieldLabel>
						<name>LpuBuilding_Code</name>
						<xtype>numberfield</xtype>
						<linked_id>elem1</linked_id>
						<value><xsl:value-of select="//LpuBuilding_id"/></value>
						<maxValue>999999</maxValue>
						<minValue>1</minValue>
						<autoCreate>
							<tag>input</tag>
							<size>14</size>
							<maxLength>6</maxLength>
							<autocomplete>off</autocomplete>
						</autoCreate>
						<id>lbLpuBuilding_Code</id>
						<allowBlank></allowBlank>
					</arrayNode>
					<arrayNode>
						<allowBlank></allowBlank>
						<editable></editable>
						<fieldLabel>Числовое поле ввода</fieldLabel>
						<hiddenName>Consult_id</hiddenName>
						<lastQuery></lastQuery>
						<linked_id>elem2</linked_id>
						<listWidth>350</listWidth>						
						<store>
							<arrayNode>
								<arrayNode>1</arrayNode>
								<arrayNode>Первый вариант</arrayNode>
							</arrayNode>
							<arrayNode>
								<arrayNode>2</arrayNode>
								<arrayNode>Второй вариант</arrayNode>
							</arrayNode>
							<arrayNode>
								<arrayNode>3</arrayNode>
								<arrayNode>Третий вариант</arrayNode>
							</arrayNode>
						</store>
						<value><xsl:value-of select="//Option_id"/></value>
						<width>250</width>
						<xtype>swbaselocalcombo</xtype>
					</arrayNode>
				</items>
			</data>
	</xsl:template>
</xsl:stylesheet>