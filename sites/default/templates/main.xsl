<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [
<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "common/xhtml-lat1.ent">
<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "common/xhtml-symbol.ent">
<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "common/xhtml-special.ent">
%lat1;
%symbol;
%special;
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:output method="xml" indent="yes" encoding="utf-8"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
		doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />

	<!-- load locale -->
	<xsl:variable name="locale" select="document ('../lang.xml')/locale" />
	<xsl:param name="locale" select="$locale"/>

	<!-- Куски страниц -->
	<xsl:include href="common/all.xsl"/>

	<!-- load more templates -->
	<xsl:include href="index.xsl" />
		
	<!-- common html part -->
	<xsl:template match="/root">
		<html>
			<head>
				<title><xsl:value-of select="$locale/seo/index/title"/></title>
				<link rel="SHORTCUT ICON" href="/favicon.ico" />
				<link rel="ICON" href="/favicon.ico" type="image/x-icon" />
				<meta name="keywords" content="{$locale/seo/index/keywords}" />
				<meta name="description" content="{$locale/seo/index/description}" />
				<meta http-equiv="pragma" content="no-cache" />
				<meta http-equiv="proxy" content="no-cache" />
				<link href="/css/style.css" rel="stylesheet" type="text/css" />
				<script type="text/javascript" src="/js/common/main.js"></script>
			</head>
			<body>
				<div class="logo"></div>
				<xsl:apply-templates select="*[not(@autorender=0)]"/>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>
