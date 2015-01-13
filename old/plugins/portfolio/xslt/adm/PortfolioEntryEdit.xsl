<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="PortfolioEntryEdit">
		<h2>
			<a href="/adm/content/portfolio">
				<xsl:value-of select="$locale/portfolio/adm/list/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/portfolio/adm/edit/title"/>
			<xsl:if test="@edit=1">
				<xsl:text> </xsl:text>
				<xsl:value-of select="$locale/portfolio/adm/edit/title-name-prefix"/>
				<xsl:value-of select="entry/@name"/>
				<xsl:value-of select="$locale/portfolio/adm/edit/title-name-postfix"/>
			</xsl:if>
		</h2>
		<xsl:call-template name="PortfolioEntryEditForm"/>
	</xsl:template>
</xsl:stylesheet>