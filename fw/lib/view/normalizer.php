<?php

return <<<NORMALIZER
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
	<xsl:output method="html" encoding="utf-8" omit-xml-declaration="yes" indent="yes"/>

	<xsl:template match="/">
		<xsl:text disable-output-escaping="yes">&lt;!DOCTYPE html&gt;</xsl:text>
		<xsl:apply-templates select="*|"/>
	</xsl:template>

	<xsl:template match="*[name()='area']|*[name()='base']|*[name()='br']|*[name()='col']|*[name()='command']|*[name()='embed']|*[name()='hr']|*[name()='img']|
	*[name()='input']|*[name()='keygen']|*[name()='link']|*[name()='meta']|*[name()='param']|*[name()='source']|*[name()='track']|*[name()='wbr']">
		<xsl:text disable-output-escaping="yes">&lt;</xsl:text>
		<xsl:value-of select="name()"/>
		<xsl:for-each select="./@*">
			<xsl:text> </xsl:text>
			<xsl:value-of select="name()"/>
			<xsl:text>="</xsl:text>
			<xsl:value-of select="."/>
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
