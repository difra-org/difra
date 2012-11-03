<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:output method="xml"
		    indent="yes"
		    encoding="utf-8"
		    doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
		    doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"/>
	<xsl:variable name="locale" select="document ('{/root/@lang}')/locale"/>
	<xsl:template match="/error404">
		<html>
			<head>
				<title>Error 404: Not found</title>
				<link rel="SHORTCUT ICON" href="/favicon.ico"/>
				<link rel="ICON" href="/favicon.ico" type="image/x-icon"/>
				<meta name="keywords" content=""/>
				<meta name="description" content=""/>
				<meta http-equiv="pragma" content="no-cache"/>
				<meta http-equiv="proxy" content="no-cache"/>
				<link href="/css/main.css" rel="stylesheet" type="text/css"/>
			</head>
			<body class="error404">
				<h2>Error 404: Not found</h2>
				<a href="/">Back to site</a>
			</body>
		</html>
	</xsl:template>
</xsl:stylesheet>