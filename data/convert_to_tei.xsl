<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
   xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
   xmlns="http://www.tei-c.org/ns/1.0">

<!-- main document template -->
<xsl:template match="/">

<TEI xmlns="http://www.tei-c.org/ns/1.0">
   <xsl:attribute name="xml:id">mira_<xsl:value-of select="manuscript/@id" /></xsl:attribute>
   <teiHeader>
      <fileDesc>
         <titleStmt>
            <title>Manuscripts with Irish Associations: catalogue item <xsl:value-of select="manuscript/@id" /></title>
         </titleStmt>
         <publicationStmt>
            <publisher>Manuscripts with Irish Associations</publisher>
            <authority>Pádraic Moran</authority>
            <availability status="free">
               <licence target="https://creativecommons.org/licenses/by-nc-sa/4.0/">
                  <p>Creative Commons BY-NC-SA 4.0</p>
               </licence>
            </availability>
         </publicationStmt>
         <notesStmt>
            <note><xsl:apply-templates select="manuscript/notes/project_notes"/></note>
         </notesStmt>
         <sourceDesc>
            <msDesc>
               <msIdentifier>
                  <settlement>@</settlement>
                  <repository key="st-paul"><xsl:attribute name="key"><xsl:value-of select="manuscript/identifier/@libraryID" /></xsl:attribute>@</repository>
                  <idno><xsl:value-of select="manuscript/identifier/shelfmark"/></idno>
                  <msName>Reichenauer Schulheft</msName>
               </msIdentifier>
               <physDesc>
                  <objectDesc>
                     <supportDesc>
                        <extent>
                           <measure type="folios"><xsl:value-of select="manuscript/description/folios"/></measure>
                           <dimensions unit="cm">
                              <height><xsl:value-of select="manuscript/description/page_h"/></height>
                              <width><xsl:value-of select="manuscript/description/page_w"/></width>
                           </dimensions>
                        </extent>
                     </supportDesc>
                  </objectDesc>
                  <handDesc>
                     <p><xsl:value-of select="manuscript/description/script"/></p>
                  </handDesc>
               </physDesc>
               <msContents>
                  <summary><xsl:apply-templates select="manuscript/description/contents"/></summary>
                  <!--
                  <msItem>
                     <locus from="" to=""></locus>
                     <author key=""></author>
                     <title key=""></title>
                     <note></note>
                  </msItem>
                  -->
               </msContents>
               <history>
                  <origin>
                     <xsl:apply-templates select="manuscript/history/origin"/>
                     <origDate>
                        <xsl:attribute name="notBefore"><xsl:value-of select="/manuscript/history/term_post"/></xsl:attribute>
                        <xsl:attribute name="notAfter"><xsl:value-of select="/manuscript/history/term_ante"/></xsl:attribute>
                        <xsl:value-of select="/manuscript/history/date_desc"/>
                     </origDate>
                  </origin>
                  <provenance>
                     <xsl:apply-templates select="manuscript/history/provenance"/>
                  </provenance>
               </history>
               <additional>
                  <xsl:attribute name="ana">#<xsl:value-of select="translate(/manuscript/notes/categories, ';', '#')"/></xsl:attribute>
                  <listBibl>
                     <bibl><ref type="images"><xsl:attribute name="target"><xsl:value-of select="manuscript/identifier/link[@type='images']"/></xsl:attribute>Link to images</ref></bibl>
                     <xsl:apply-templates select="manuscript/xrefs"/>
                  </listBibl>
               </additional>
            </msDesc>
         </sourceDesc>
      </fileDesc>
      <revisionDesc>
         <change who="Pádraic Moran" when="2021-05-01">Created</change>
      </revisionDesc>
   </teiHeader>
   <text>
      <body>
         <p/>
      </body>
   </text>
</TEI>

</xsl:template>

<!-- mini templates -->
<xsl:template match="i">
   <title><xsl:value-of select="."/></title>
</xsl:template>

<xsl:template match="link">
   <bibl><ref>
      <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
      <xsl:attribute name="target"><xsl:value-of select="@href" /></xsl:attribute>
      <xsl:value-of select="."/></ref></bibl>
</xsl:template>

<xsl:template match="ms">
   <ref><xsl:attribute name="target">mira_<xsl:value-of select="@id" /></xsl:attribute><xsl:value-of select="."/></ref>
</xsl:template>

<xsl:template match="person">
   <persName role="author"><xsl:attribute name="key"><xsl:value-of select="@id" /></xsl:attribute><xsl:value-of select="."/></persName>
</xsl:template>

<xsl:template match="place">
   <placeName><xsl:attribute name="key"><xsl:value-of select="@id" /></xsl:attribute><xsl:value-of select="."/></placeName>
</xsl:template>

<xsl:template match="xref">
   <bibl><xsl:attribute name="corresp">#<xsl:value-of select="@type" /></xsl:attribute><xsl:value-of select="."/></bibl>
</xsl:template>

</xsl:stylesheet>
