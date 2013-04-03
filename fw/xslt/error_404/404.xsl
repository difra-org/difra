<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
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