<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
   xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
   xmlns="http://www.tei-c.org/ns/1.0">

   <xsl:output method="xml" indent="yes"/>
   <xsl:strip-space elements="*"/>
   <xsl:preserve-space elements="history"/>

<!-- main document template -->
<xsl:template match="/">

<TEI xmlns="http://www.tei-c.org/ns/1.0"
   xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
   xsi:schemaLocation="http://www.tei-c.org/ns/1.0 http://www.tei-c.org/release/xml/tei/custom/schema/xsd/tei_all.xsd">
   <xsl:attribute name="xml:id">mira_<xsl:value-of select="manuscript/@id" /></xsl:attribute>
   <teiHeader>
      <fileDesc>

         <!-- title of the resource -->
         <xsl:comment>title of the resource</xsl:comment>
         <titleStmt>
            <title>Manuscripts with Irish Associations: catalogue item <xsl:value-of select="manuscript/@id" /></title>
         </titleStmt>

         <!-- information about distribution -->
         <xsl:comment>information about distribution</xsl:comment>
         <publicationStmt>
            <publisher>Manuscripts with Irish Associations</publisher>
            <authority>Pádraic Moran</authority>
            <availability status="free">
               <licence target="https://creativecommons.org/licenses/by-nc-sa/4.0/">
                  <p>Creative Commons BY-NC-SA 4.0</p>
               </licence>
            </availability>
         </publicationStmt>

         <!-- notes on this manuscript -->
         <xsl:comment>notes on this manuscript</xsl:comment>
         <notesStmt>
            <note><xsl:apply-templates select="manuscript/notes"/>
            </note>
         </notesStmt>

         <!-- main manuscript description -->
         <xsl:comment>main manuscript description</xsl:comment>
         <sourceDesc>
            <msDesc>
               <!-- identifiers -->
               <xsl:choose>
                  <xsl:when test="count(manuscript/identifier) = 1">
                     <xsl:comment>identifier</xsl:comment>
                     <xsl:apply-templates select="manuscript/identifier" mode="single-identifier"/>
                  </xsl:when>
                  <xsl:when test="count(manuscript/identifier) > 1">
                     <xsl:comment>identifiers</xsl:comment>
                     <msIdentifier></msIdentifier>
                     <xsl:apply-templates select="manuscript/identifier" mode="multiple-identifiers"/>
                  </xsl:when>
               </xsl:choose>

               <!-- physical description: folios, dimensions, scripts -->
               <xsl:comment>physical description: folios, dimentions, scripts</xsl:comment>
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

               <!-- text contents -->
               <xsl:comment>text contents</xsl:comment>
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

               <!-- history: origin, provenance -->
               <xsl:comment>history: origin, provenance</xsl:comment>
               <history>
                  <origin>
                     <xsl:apply-templates select="manuscript/history/origin"/>
                     <origDate>
                        <xsl:attribute name="notBefore"><xsl:value-of select="format-number(/manuscript/history/term_post, '0000')"/></xsl:attribute>
                        <xsl:attribute name="notAfter"><xsl:value-of select="format-number(/manuscript/history/term_ante, '0000')"/></xsl:attribute>
                        <xsl:value-of select="/manuscript/history/date_desc"/>
                     </origDate>
                  </origin>
                  <provenance>
                     <xsl:apply-templates select="manuscript/history/provenance"/>
                  </provenance>
               </history>

               <!-- bibliographical references (list format) -->
               <!--  
                  # Pausing bibliographical references for nows

               <xsl:comment>bibliographical references (list format)</xsl:comment>
               <additional>
                  <xsl:attribute name="ana"><xsl:value-of select="/manuscript/notes/@categories"/></xsl:attribute>
                  <listBibl>
                     <bibl>Link to images online <ptr type="images"><xsl:attribute name="target"><xsl:value-of select="manuscript/ref/link[@type='images']"/></xsl:attribute></ptr></bibl>
                     <xsl:apply-templates select="manuscript/listBibl"/>
                  </listBibl>
               </additional>
               -->
            </msDesc>
         </sourceDesc>
      </fileDesc>

      <!-- revision history -->
      <xsl:comment>revision history</xsl:comment>
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

<!-- section templates -->
<xsl:template match="identifier" mode="single-identifier">
   <xsl:variable name="libID" select="@libraryID"/>
   <xsl:variable name="lib" select="document('../../data/libraries.xml')//library[@id = $libID]"/>

   <msIdentifier>
      <settlement><xsl:value-of select="$lib/city"/></settlement>
      <repository key="{$libID}"><xsl:value-of select="$lib/name"/></repository>
      <idno><xsl:value-of select="./shelfmark"/></idno>
      <msName><xsl:value-of select="ms_name"/></msName>
   </msIdentifier>
</xsl:template>

<xsl:template match="identifier" mode="multiple-identifiers">
   <xsl:variable name="libID" select="@libraryID"/>
   <xsl:variable name="lib" select="document('../../data/libraries.xml')//library[@id = $libID]"/>

   <msFrag>
      <msIdentifier>
         <settlement><xsl:value-of select="$lib/city"/></settlement>
         <repository key="{$libID}"><xsl:value-of select="$lib/name"/></repository>
         <idno><xsl:value-of select="./shelfmark"/></idno>
         <msName><xsl:value-of select="ms_name"/></msName>
      </msIdentifier>
   </msFrag>
</xsl:template>


<!-- mini templates -->
<xsl:template match="i">
   <title><xsl:value-of select="."/></title>
</xsl:template>

<xsl:template match="link">
   <bibl><ptr>
      <xsl:attribute name="type"><xsl:value-of select="@type" /></xsl:attribute>
      <xsl:attribute name="target"><xsl:value-of select="@href" /></xsl:attribute>
      <xsl:value-of select="."/></ptr></bibl>
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
