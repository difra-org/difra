<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:output method="xml" indent="yes" encoding="utf-8"
		doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
		doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN" />

	<!-- common html part -->
	<xsl:template match="/root">
		<html>
			<head>
				<title>Панель администратора</title>
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
				<xsl:apply-templates select="menu">
					<xsl:with-param name="auto" select="0"/>
				</xsl:apply-templates>
				<xsl:apply-templates select="*"/>
			</body>
		</html>
	</xsl:template>

</xsl:stylesheet>

