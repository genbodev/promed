<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version = '1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
	<xsl:template match="/">
			 <data>
				<arrayNode>
					<xtype>label</xtype>
					<text><xsl:value-of select="//DocumentDate"/></text>
					<linked_id>DocumentDate</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>label</xtype>
					<text><xsl:value-of select="//DocumentTime"/></text>
					<linked_id>DocumentTime</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>label</xtype>
					<text><xsl:value-of select="//Person_FIO"/></text>
					<linked_id>Person_FIO</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>textarea</xtype>
					<name>Jaloby</name>
					<width>600</width>
					<value><xsl:value-of select="//Jaloby"/></value>
					<linked_id>Jaloby</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>textarea</xtype>
					<name>Anamnes_Zab</name>
					<width>600</width>
					<value><xsl:value-of select="//Anamnes_Zab"/></value>
					<linked_id>Anamnes_Zab</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>textarea</xtype>
					<name>Anamnez_Jizn</name>
					<width>600</width>
					<value><xsl:value-of select="//Anamnez_Jizn"/></value>
					<linked_id>Anamnez_Jizn</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>textarea</xtype>
					<name>Expert_Anamn</name>
					<width>600</width>
					<value><xsl:value-of select="//Expert_Anamn"/></value>
					<linked_id>Expert_Anamn</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>textarea</xtype>
					<name>Object_Stat</name>
					<width>600</width>
					<value><xsl:value-of select="//Object_Stat"/></value>
					<linked_id>Object_Stat</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>textarea</xtype>
					<name>Local_Stat</name>
					<width>600</width>
					<value><xsl:value-of select="//Local_Stat"/></value>
					<linked_id>Local_Stat</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>Brah_Stvol</name>
					<checked><xsl:value-of select="//Brah_Stvol"/></checked>
					<linked_id>Brah_Stvol</linked_id>
				</arrayNode>				
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>SonPulsR</name>
					<checked><xsl:value-of select="//SonPulsR"/></checked>
					<linked_id>SonPulsR</linked_id>
				</arrayNode>				
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>SonPulsL</name>
					<checked><xsl:value-of select="//SonPulsL"/></checked>
					<linked_id>SonPulsL</linked_id>
				</arrayNode>				
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>SonShumR</name>
					<checked><xsl:value-of select="//SonShumR"/></checked>
					<linked_id>SonShumR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>SonShumL</name>
					<checked><xsl:value-of select="//SonShumL"/></checked>
					<linked_id>SonShumL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>VisArtPulsR</name>
					<checked><xsl:value-of select="//VisArtPulsR"/></checked>
					<linked_id>VisArtPulsR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>VisArtPulsL</name>
					<checked><xsl:value-of select="//VisArtPulsL"/></checked>
					<linked_id>VisArtPulsL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PozvonArtShumR</name>
					<checked><xsl:value-of select="//PozvonArtShumR"/></checked>
					<linked_id>PozvonArtShumR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PozvonArtShumL</name>
					<checked><xsl:value-of select="//PozvonArtShumsL"/></checked>
					<linked_id>PozvonArtShumL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodkluchArtPulsR</name>
					<checked><xsl:value-of select="//PodkluchArtPulsR"/></checked>
					<linked_id>PodkluchArtPulsR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodkluchArtPulsL</name>
					<checked><xsl:value-of select="//PodkluchArtPulsL"/></checked>
					<linked_id>PodkluchArtPulsL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodkluchArtShumR</name>
					<checked><xsl:value-of select="//PodkluchArtShumR"/></checked>
					<linked_id>PodkluchArtShumR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodkluchArtShumL</name>
					<checked><xsl:value-of select="//PodkluchArtShumL"/></checked>
					<linked_id>PodkluchArtShumL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodmyshArtPulsR</name>
					<checked><xsl:value-of select="//PodmyshArtPulsR"/></checked>
					<linked_id>PodmyshArtPulsR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodmyshArtPulsL</name>
					<checked><xsl:value-of select="//PodmyshArtPulsL"/></checked>
					<linked_id>PodmyshArtPulsL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>LuchArtPulsR</name>
					<checked><xsl:value-of select="//LuchArtPulsR"/></checked>
					<linked_id>LuchArtPulsR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PlechArtPulsL</name>
					<checked><xsl:value-of select="//PlechArtPulsL"/></checked>
					<linked_id>PlechArtPulsL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PlechArtPulsR</name>
					<checked><xsl:value-of select="//PlechArtPulsR"/></checked>
					<linked_id>PlechArtPulsR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>LuchArtPulsL</name>
					<checked><xsl:value-of select="//LuchArtPulsL"/></checked>
					<linked_id>LuchArtPulsL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>PodkluchArtShumL</name>
					<checked><xsl:value-of select="//PodkluchArtShumL"/></checked>
					<linked_id>PodkluchArtShumL</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>LoktArtPulsR</name>
					<checked><xsl:value-of select="//LoktArtPulsR"/></checked>
					<linked_id>LoktArtPulsR</linked_id>
				</arrayNode>
				<arrayNode>
					<xtype>checkbox</xtype>
					<name>LoktArtPulsL</name>
					<checked><xsl:value-of select="//LoktArtPulsL"/></checked>
					<linked_id>LoktArtPulsL</linked_id>
				</arrayNode>
				<arrayNode>
					<hideLabel>true</hideLabel>
					<hiddenName>Consult_id</hiddenName>
					<linked_id>Consult</linked_id>
					<listWidth>450</listWidth>
					<width>450</width>
					<store>
						<arrayNode>
							<arrayNode>1</arrayNode>
							<arrayNode>Заведующим отделением</arrayNode>
						</arrayNode>
						<arrayNode>
							<arrayNode>2</arrayNode>
							<arrayNode>Ответственным хирургом</arrayNode>
						</arrayNode>						
					</store>
					<value><xsl:value-of select="//Consult"/></value>
					<xtype>swbaselocalcombo</xtype>
				</arrayNode>
			</data>
	</xsl:template>
</xsl:stylesheet>