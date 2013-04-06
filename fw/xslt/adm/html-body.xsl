<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template name="html-body">
		<body>
			<xsl:call-template name="content-wrapper"/>
			<xsl:apply-templates select="root/menu[@instance='adm']">
				<xsl:with-param name="auto" select="0"/>
			</xsl:apply-templates>
		</body>
	</xsl:template>
</xsl:stylesheet>