<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template name="html-head">
		<head>
			<title>
				<xsl:call-template name="html-head-title"/>
			</title>

			<!-- favicon -->
			<xsl:text disable-output-escaping="yes">&lt;link rel="SHORTCUT ICON" href="/favicon.ico"/&gt;</xsl:text>
			<xsl:text disable-output-escaping="yes">&lt;link rel="ICON" href="/favicon.ico" type="image/x-icon"/&gt;</xsl:text>

			<!-- meta name="keywords" -->
			<xsl:text disable-output-escaping="yes">&lt;meta name="keywords" content="</xsl:text>
			<xsl:value-of select="$locale/seo/index/keywords"/>
			<xsl:text disable-output-escaping="yes">"&gt;</xsl:text>

			<!-- meta name="description" -->
			<xsl:text disable-output-escaping="yes">&lt;meta name="description" content="</xsl:text>
			<xsl:value-of select="$locale/seo/index/description"/>
			<xsl:text disable-output-escaping="yes">"&gt;</xsl:text>

			<!-- link type="text/css" href="/css/main.css" -->
			<xsl:text disable-output-escaping="yes">&lt;link type="text/css" href="</xsl:text>
			<xsl:value-of select="/root/@urlprefix"/>
			<xsl:text>/css/main.css?</xsl:text>
			<xsl:value-of select="/root/@build"/>
			<xsl:text disable-output-escaping="yes">" rel="stylesheet"&gt;</xsl:text>

			<!-- script type="text/javascript" src="/js/main.js" -->
			<script type="text/javascript" src="{/root/@urlprefix}/js/main.js?{/root/@build}"/>

			<script type="text/javascript">
				<xsl:text>var config={};</xsl:text>
				<xsl:value-of select="/root/@jsConfig"/>
			</script>

			<xsl:call-template name="html-head-additional"/>
		</head>
	</xsl:template>

	<xsl:template name="html-head-additional">
	</xsl:template>

	<xsl:template name="html-head-title">
		<xsl:value-of select="$locale/seo/index/title"/>
		<xsl:if test="/root/@title and not(/root/@title='')">
			<xsl:text>&#160;—&#160;</xsl:text>
			<xsl:value-of select="/root/@title"/>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>