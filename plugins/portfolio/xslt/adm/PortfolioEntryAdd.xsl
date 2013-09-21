<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="PortfolioEntryAdd">
		<h2>
			<a href="/adm/content/portfolio">
				<xsl:value-of select="$locale/portfolio/adm/list/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/portfolio/adm/add/title"/>
		</h2>
		<xsl:call-template name="PortfolioEntryEditForm"/>
	</xsl:template>
</xsl:stylesheet>