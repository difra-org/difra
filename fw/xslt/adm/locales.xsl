<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/locales">
		<h2>
			<xsl:value-of select="$locale/adm/locales/title"/>
		</h2>
	</xsl:template>
</xsl:stylesheet>