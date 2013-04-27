<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="/root">
		<xsl:choose>
			<xsl:when test="@standalone">
				<html>
					<head>
						<title>Debug page</title>
						<link rel="SHORTCUT ICON" href="/favicon.ico"/>
						<link rel="ICON" href="/favicon.ico" type="image/x-icon"/>
						<meta name="keywords" content=""/>
						<meta name="description" content=""/>
						<meta http-equiv="pragma" content="no-cache"/>
						<meta http-equiv="proxy" content="no-cache"/>
						<link href="/css/console.css" rel="stylesheet" type="text/css"/>
						<script type="text/javascript" src="/js/main.js"/>
						<script type="text/javascript" src="/js/console.js"/>
					</head>
					<body>
						<xsl:apply-templates select="/root/debug"/>
					</body>
				</html>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="/root/debug"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>