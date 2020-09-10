<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:template match="/">
    <data>
      <xtype>fieldset</xtype>
      <autoHeight>true</autoHeight>
      <region>center</region>
      <style>border: 0;</style>
      <items>
        <arrayNode>
          <defaults>
            <style>padding: 5px;</style>
          </defaults>
          <autoHeight>true</autoHeight>
          <bodyStyle>padding: 5px; border: 0; text-align: center; font-weight: bold; font-size: 14pt</bodyStyle>
          <items>
            <arrayNode>
              <xtype>label</xtype>
              <text>Комплексная услуга</text>
            </arrayNode>
          </items>
        </arrayNode>
        <arrayNode>
          <fieldLabel>Комплексная услуга (нажмите на стрелку справа, чтобы развернуть панель инструментов)</fieldLabel>
          <xtype>ckeditor</xtype>
          <name>UserTemplateData</name>
          <height>300</height>
          <defaultValue>
            {template}
          </defaultValue>
          <value>
            <xsl:value-of select="//UserTemplateData" />
          </value>
        </arrayNode>
      </items>
    </data>
  </xsl:template>
</xsl:stylesheet>