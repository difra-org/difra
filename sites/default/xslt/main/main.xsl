<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:output method="xml" indent="yes" encoding="utf-8"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
		doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />

	<xsl:template name="page">
		<html>
			<head>
				<title><xsl:value-of select="$locale/seo/index/title"/></title>
				<link rel="SHORTCUT ICON" href="/favicon.ico" />
				<link rel="ICON" href="/favicon.ico" type="image/x-icon" />
				<meta name="keywords" content="{$locale/seo/index/keywords}" />
				<meta name="description" content="{$locale/seo/index/description}" />
				<meta http-equiv="pragma" content="no-cache" />
				<meta http-equiv="proxy" content="no-cache" />
				<link href="/css/main.css?{/root/@build}" rel="stylesheet" type="text/css" />
				<script type="text/javascript" src="/js/common/main.js?{/root/@build}"></script>
			</head>
			<body>
				<h1>
					<xsl:value-of select="$locale/index/welcome"/>
				</h1>
				<xsl:call-template name="content"/>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="ajax-page">
		<html>
			<head>
				<title>
					<xsl:value-of select="$locale/seo/index/title"/>
					<xsl:if test="/root/@title">
						<xsl:text> — </xsl:text>
						<xsl:value-of select="/root/@title"/>
					</xsl:if>
				</title>
			</head>
			<body>
				<xsl:call-template name="content"/>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="content">
		<div id="content">
			<xsl:apply-templates select="*[not(@autorender=0)]"/>
		</div>
	</xsl:template>

	<xsl:template match="/root">
		<xsl:choose>
			<xsl:when test="/root/@ajax=1">
				<xsl:call-template name="ajax-page"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="page"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

</xsl:stylesheet>
