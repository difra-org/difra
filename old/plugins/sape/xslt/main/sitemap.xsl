<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="/root/sitemap">
		<xsl:if test="@html and not(@html='')">
			<div style="display:none">
				<xsl:value-of select="@html" disable-output-escaping="yes"/>
			</div>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>