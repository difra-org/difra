<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="catalog-bc">
		<xsl:param name="parent" select="0"/>
		<xsl:param name="withLast" select="0"/>
		<xsl:if test="$parent &gt; 0 or /root/content/catalogCategories/category[@parent=$parent and @selected=1]">
			<xsl:variable name="cat" select="/root/content/catalogCategories/category[@parent=$parent and @selected=1]"/>
			<xsl:choose>
				<xsl:when test="/root/content/catalogCategories/category[@parent=$cat/@id and @selected=1]">
					<a href="{$cat/@link}">
						<xsl:value-of select="$cat/@name"/>
					</a>
					<xsl:text> → </xsl:text>
					<xsl:call-template name="catalog-bc">
						<xsl:with-param name="parent" select="$cat/@id"/>
						<xsl:with-param name="withLast" select="$withLast"/>
					</xsl:call-template>
				</xsl:when>
				<xsl:when test="$withLast=1">
					<a href="{$cat/@link}">
						<xsl:value-of select="$cat/@name"/>
					</a>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$cat/@name"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
