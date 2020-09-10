<?xml version="1.0" encoding="windows-1251"?>
<xsl:stylesheet version = '1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform'>
	<xsl:template match="/">
			 <data>
				<xtype>fieldset</xtype>
				<autoHeight>true</autoHeight>
				<region>center</region>
				<items>					
					<!--<arrayNode>
						<defaults><style>padding: 5px;</style></defaults>
						<autoHeight>true</autoHeight>
						<bodyStyle>padding: 5px; border: 0; text-align: right</bodyStyle>						
						<items>
							<arrayNode>
								<xtype>label</xtype>
								<text>����: <xsl:value-of select="//DocumentDate"/> </text>
							</arrayNode>
							<arrayNode>
								<xtype>label</xtype>
								<text> �����: <xsl:value-of select="//DocumentTime"/> </text>
							</arrayNode>
							<arrayNode>
								<xtype>label</xtype>
								<text> �������: <xsl:value-of select="//Person_FIO"/> </text>
							</arrayNode>
						</items>
					</arrayNode>-->
					<arrayNode>
						<defaults><style>padding: 5px;</style></defaults>
						<autoHeight>true</autoHeight>
						<bodyStyle>padding: 5px; border: 0; text-align: center; font-weight: bold; font-size: 14pt</bodyStyle>						
						<items>
							<arrayNode>
								<xtype>label</xtype>
								<text>������ ����� ��� �������������� �������</text>
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
								<fieldLabel>������</fieldLabel>
								<xtype>textarea</xtype>
								<name>Jaloby</name>
								<width>600</width>
								<value><xsl:value-of select="//Jaloby"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>������� �����������</fieldLabel>
								<xtype>textarea</xtype>
								<name>Anamnes_Zab</name>
								<width>600</width>
								<value><xsl:value-of select="//Anamnes_Zab"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>������� �����</fieldLabel>
								<xtype>textarea</xtype>
								<id>id_Anamnez_Jizn</id>
								<name>Anamnez_Jizn</name>
								<width>600</width>
								<value><xsl:value-of select="//Anamnez_Jizn"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>���������� �������</fieldLabel>
								<xtype>textarea</xtype>
								<name>Expert_Anamn</name>
								<width>600</width>
								<value><xsl:value-of select="//Expert_Anamn"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>����������� ������</fieldLabel>
								<xtype>textarea</xtype>
								<name>Object_Stat</name>
								<width>600</width>
								<value><xsl:value-of select="//Object_Stat"/></value>
							</arrayNode>
							<arrayNode>
								<fieldLabel>��������� ������</fieldLabel>
								<xtype>textarea</xtype>
								<name>Local_Stat</name>
								<width>600</width>
								<value><xsl:value-of select="//Local_Stat"/></value>
							</arrayNode>
						</items>
					</arrayNode>
					<arrayNode>
						<xtype>panel</xtype>
						<layout>table</layout>
						<layoutConfig>
							<columns>5</columns>
							<style>border-collapse: collapse</style>
						</layoutConfig>
						<bodyStyle>border: 0;</bodyStyle>
						<defaults><bodyStyle>border: 0; padding: 5px;</bodyStyle></defaults>
					</arrayNode>
					<arrayNode>
						<xtype>panel</xtype>
						<layout>table</layout>
						<layoutConfig>
							<columns>5</columns>
							<style>border-collapse: collapse</style>
						</layoutConfig>
						<bodyStyle>border: 0;</bodyStyle>
						<defaults><bodyStyle>border: 0; padding: 5px;</bodyStyle></defaults>
						<items>
							<arrayNode>
								<html>���������� �������</html>
								<colspan>5</colspan>
								<style>text-align: center; font-weight: bold</style>
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<html>������� ��������� �����</html>						
								<cellCls>borderedCell</cellCls>
							</arrayNode>
							<arrayNode>
								<html>������� ��������� ������</html>							
								<cellCls>borderedCell</cellCls>															
							</arrayNode>
							<arrayNode>
								<html>������� ���� �����</html>							
								<cellCls>borderedCell</cellCls>															
							</arrayNode>
							<arrayNode>
								<html>������� ���� ������</html>							
								<cellCls>borderedCell</cellCls>															
							</arrayNode>							
							<arrayNode>
								<html>���������.  �����</html>	
								<cellCls>borderedCell</cellCls>
							</arrayNode>							
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>							
							<arrayNode>
								<html> </html>		
								<cellCls>borderedCell</cellCls>						
							</arrayNode>							
							<arrayNode>
								<html> </html>							
								<cellCls>borderedCell</cellCls>															
							</arrayNode>							
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>Brah_Stvol</name>
										<checked><xsl:value-of select="//Brah_Stvol"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>															
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<html>������ �������</html>		
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>							
										<xtype>checkbox</xtype>
										<name>SonPulsR</name>
										<checked><xsl:value-of select="//SonPulsR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>															
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>							
										<xtype>checkbox</xtype>
										<name>SonPulsL</name>
										<checked><xsl:value-of select="//SonPulsL"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>								
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>							
										<xtype>checkbox</xtype>
										<name>SonShumR</name>
										<checked><xsl:value-of select="//SonShumR"/></checked>
									</arrayNode>									
								</items>		
								<cellCls>borderedCell</cellCls>															
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>							
										<xtype>checkbox</xtype>
										<name>SonShumL</name>
										<checked><xsl:value-of select="//SonShumL"/></checked>
									</arrayNode>
								</items>			
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>								
							</arrayNode>
							<arrayNode>
								<html>�������� �������</html>		
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>VisArtPulsR</name>
										<checked><xsl:value-of select="//VisArtPulsR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>
								<style>text-align: center</style>								
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>VisArtPulsL</name>
										<checked><xsl:value-of select="//VisArtPulsL"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<html>����������� �������</html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PozvonArtPulsR</name>
										<checked><xsl:value-of select="//PozvonArtPulsR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>
								<style>text-align: center</style>								
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PozvonArtPulsL</name>
										<checked><xsl:value-of select="//PozvonArtPulsL"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<html>������������ �������</html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PodkluchArtPulsR</name>
										<checked><xsl:value-of select="//PodkluchArtPulsR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>
								<style>text-align: center</style>								
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PodkluchArtPulsL</name>
										<checked><xsl:value-of select="//PodkluchArtPulsL"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PodkluchArtShumR</name>
										<checked><xsl:value-of select="//PodkluchArtShumR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PodkluchArtShumL</name>
										<checked><xsl:value-of select="//PodkluchArtShumL"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<html>����������� �������</html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PodmyshArtPulsR</name>
										<checked><xsl:value-of select="//PodmyshArtPulsR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>
								<style>text-align: center</style>								
							</arrayNode>
							<arrayNode>
								<items>
									<arrayNode>
										<xtype>checkbox</xtype>
										<name>PodmyshArtPulsL</name>
										<checked><xsl:value-of select="//PodmyshArtPulsR"/></checked>
									</arrayNode>
								</items>
								<cellCls>borderedCell</cellCls>	
								<style>text-align: center</style>
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>
							<arrayNode>
								<html> </html>
								<cellCls>borderedCell</cellCls>							
							</arrayNode>							
						</items>
					</arrayNode>
					<arrayNode>
						<xtype>panel</xtype>
						<layout>table</layout>
						<layoutConfig>
							<columns>3</columns>
							<style>border-collapse: collapse; margin: 5px</style>
						</layoutConfig>
						<bodyStyle>border: 0; margin: 10px; margin-left: 0</bodyStyle>
						<defaults><bodyStyle>border: 0; padding: 5px;</bodyStyle></defaults>
						<items>
							<arrayNode>
								<xtype>label</xtype>
								<text>�������������� </text>
								<style>padding: 5px;</style>
							</arrayNode>
							<arrayNode>
								<hideLabel>true</hideLabel>
								<hiddenName>Consult_id</hiddenName>
								<listWidth>450</listWidth>
								<width>450</width>
								<store>
									<arrayNode>
										<arrayNode>1</arrayNode>
										<arrayNode>���������� ����������</arrayNode>
									</arrayNode>
									<arrayNode>
										<arrayNode>2</arrayNode>
										<arrayNode>������������� ��������</arrayNode>
									</arrayNode>						
								</store>
								<value><xsl:value-of select="//Consult"/></value>
								<xtype>swbaselocalcombo</xtype>
							</arrayNode>
							<arrayNode>
								<xtype>label</xtype>
								<text> , ������� �����������.</text>
								<style>padding: 5px;</style>
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
								<text>����: _____________________</text>
							</arrayNode>
						</items>
					</arrayNode>					
				</items>
			</data>
	</xsl:template>
</xsl:stylesheet>