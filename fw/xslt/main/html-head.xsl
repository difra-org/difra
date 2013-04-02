<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="html-head">
		<head>
			<title>
				<xsl:call-template name="html-head-title"/>
			</title>
			<link rel="SHORTCUT ICON" href="/favicon.ico"/>
			<link rel="ICON" href="/favicon.ico" type="image/x-icon"/>
			<meta name="keywords" content="{$locale/seo/index/keywords}"/>
			<meta name="description" content="{$locale/seo/index/description}"/>
			<meta http-equiv="cache-control" content="no-cache"/>
			<link type="text/css"
			      href="{/root/@urlprefix}/css/main.css?{/root/@build}"
			      rel="stylesheet"/>
			<script type="text/javascript" src="{/root/@urlprefix}/js/main.js?{/root/@build}"/>
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