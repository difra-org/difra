<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template name="repeat">
		<xsl:param name="times"/>
		<xsl:param name="text"/>
		<xsl:if test="$times &gt; 0">
			<xsl:value-of select="$text"/>
			<xsl:call-template name="repeat">
				<xsl:with-param name="times">
					<xsl:value-of select="$times - 1"/>
				</xsl:with-param>
				<xsl:with-param name="text">
					<xsl:value-of select="$text"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
