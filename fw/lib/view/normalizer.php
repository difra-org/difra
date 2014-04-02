<?php

/**
 * This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
 *
 * @copyright © A-Jam Studio
 * @license   http://ajamstudio.com/difra/license
 */

return <<<NORMALIZER
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes" indent="yes"/>

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<xsl:apply-templates select="*|"/>
	</xsl:template>

	<xsl:template name="escapeQuote">
		<xsl:param name="pText" select="."/>

		<xsl:if test="string-length(\$pText) >0">
			<xsl:choose>
				<xsl:when test="contains(\$pText, '&quot;')">
					<xsl:value-of select="substring-before(\$pText, '&quot;')"/>
					<xsl:text disable-output-escaping="yes">&amp;quot;</xsl:text>
					<xsl:call-template name="escapeQuote">
						<xsl:with-param name="pText" select="substring-after(\$pText, '&quot;')"/>
					</xsl:call-template>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="\$pText"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<xsl:template match="*[name()='area']|*[name()='base']|*[name()='br']|*[name()='col']|*[name()='command']|*[name()='embed']|*[name()='hr']|*[name()='img']|
	*[name()='input']|*[name()='keygen']|*[name()='link']|*[name()='meta']|*[name()='param']|*[name()='source']|*[name()='track']|*[name()='wbr']">
		<xsl:text disable-output-escaping="yes">&lt;</xsl:text>
		<xsl:value-of select="name()"/>
		<xsl:for-each select="./@*">
			<xsl:text> </xsl:text>
			<xsl:value-of select="name()"/>
			<xsl:text>="</xsl:text>
			<xsl:call-template name="escapeQuote"/>
			<xsl:text>"</xsl:text>
		</xsl:for-each>
		<xsl:text disable-output-escaping="yes">&gt;</xsl:text>
	</xsl:template>

	<xsl:template match="*">
		<xsl:copy>
			<xsl:apply-templates select="./*|@*|text()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="@*">
		<xsl:copy-of select="."/>
	</xsl:template>
</xsl:stylesheet>
NORMALIZER;
