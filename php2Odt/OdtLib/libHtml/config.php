<?php
/**
 * Description of config
 * 
 * Date - $Date: 2012-12-03 13:00:00 +0200 (lun., 03 décembre 2012) $
 * @author Sophien
 */

 /*
 * @copyright  GPL License 2012 - Mehboub Sophien - Badreddine Zeghiche - sociaNova (http://www.socianova.com)
 * @license    http://www.gnu.org/licenses/gpl-3.0.fr.html  GPL License
 * @version 1.0
 */
class config {

    static public $xsl = '<?xml version="1.0" encoding="ISO-8859-1"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0">
<xsl:output method="xml" omit-xml-declaration="yes" version="1.0" encoding="ISO-8859-1" indent="yes"/>
    <xsl:template match="/">
    <xsl:element name="body">
                <xsl:apply-templates/>
    </xsl:element>
    </xsl:template>
    
    <xsl:template match="html">
        <xsl:apply-templates/>
    </xsl:template>
    
<xsl:template match="//ul"> 
                     <xsl:variable name="expression">
     <xsl:for-each select="descendant-or-self::*">
        <xsl:variable name="name" select="name()"/>
        <xsl:value-of select="$name"/>
     </xsl:for-each>
  </xsl:variable>
  <xsl:element name="text:span">
            <xsl:attribute name="text:style-name"><xsl:value-of select="$expression"/></xsl:attribute><xsl:element name="text:line-break"/>
                 <xsl:for-each select="li">
            <xsl:element name="text:tab"/><xsl:text>* </xsl:text><xsl:value-of select="."/><xsl:element name="text:line-break"/>
                 </xsl:for-each>
        </xsl:element>
     </xsl:template>
     
<xsl:template match="//ol"> 
                     <xsl:variable name="expression">
     <xsl:for-each select="descendant-or-self::*">
        <xsl:variable name="name" select="name()"/>
        <xsl:value-of select="$name"/>
     </xsl:for-each>
  </xsl:variable>
      <xsl:variable name="number">
<xsl:number count="*" format="1"/>
  </xsl:variable>
  <xsl:element name="text:span">
            <xsl:attribute name="text:style-name"><xsl:value-of select="$expression"/></xsl:attribute><xsl:element name="text:line-break"/>
                 <xsl:for-each select="li">
            <xsl:element name="text:tab"/><xsl:number select="$number"/><xsl:text>. </xsl:text><xsl:value-of select="."/><xsl:element name="text:line-break"/>
                 </xsl:for-each>
        </xsl:element>
     </xsl:template>


<xsl:template match="node()">     
<xsl:if test="local-name()!=\'\'">
<xsl:variable name="expression">
     <xsl:for-each select="descendant-or-self::*">
        <xsl:variable name="name" select="name()"/>
        <xsl:value-of select="concat($name,@size,@color)"/>
     </xsl:for-each>
  </xsl:variable>
    <xsl:variable name="number">
<xsl:number count="*" format="1"/>
  </xsl:variable>
<xsl:element name="text:span">
            <xsl:attribute name="text:style-name"><xsl:value-of select="$expression"/></xsl:attribute>
            <xsl:value-of select="."/>
        </xsl:element>
        </xsl:if> 
</xsl:template>
</xsl:stylesheet>';
    static public $style = array('u' => array(
            'style:text-underline-style' => 'solid',
            'style:text-underline-width' => 'auto',
            'style:text-underline-color' => 'font-color'),
        'i' => array(
            'style:font-style-asian' => 'italic',
            'fo:font-style' => 'italic',
            'style:font-style-complex' => 'italic'),
        'b' => array(
            'fo:font-weight' => 'bold',
            'style:font-weight-asian' => 'bold',
            'style:font-weight-complex' => 'bold'),
        'font-color' => array(
            'fo:color'),
        'font-size' => array(
            'fo:font-size',
            'style:font-size-asian',
            'style:font-size-complex')
    );

}

?>
