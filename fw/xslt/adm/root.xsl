<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<!-- common html part -->
	<xsl:template match="/">
		<html>
			<head>
				<title>
					<xsl:value-of select="$locale/adm/global-title"/>
				</title>
				<link rel="SHORTCUT ICON" href="/favicon.ico" />
				<link rel="ICON" href="/favicon.ico" type="image/x-icon" />
				<meta name="keywords" content="" />
				<meta name="description" content="" />
				<meta http-equiv="pragma" content="no-cache" />
				<meta http-equiv="proxy" content="no-cache" />
				<link href="/css/adm.css" rel="stylesheet" type="text/css" />
				<script type="text/javascript" src="/js/adm.js"/>
			</head>
			<body>
				<xsl:apply-templates select="root/menu">
					<xsl:with-param name="auto" select="0"/>
				</xsl:apply-templates>
				<div id="content" basepath="/adm">
					<xsl:apply-templates select="root/content/*"/>
				</div>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>

