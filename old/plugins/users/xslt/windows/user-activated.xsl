<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="root/user-activated">
		<div class="userActivated">
			<h2>
				<xsl:value-of select="$locale/activation/title"/>
			</h2>

			<div class="message">

				<xsl:choose>
					<xsl:when test="@badActivation and @badActivation=1">
						<xsl:value-of select="$locale/activation/bad"/>
					</xsl:when>
					<xsl:when test="@authActivation and @authActivation=1">
						<xsl:value-of select="$locale/activation/auth"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$locale/activation/done"/>
					</xsl:otherwise>
				</xsl:choose>

			</div>
		</div>
	</xsl:template>

</xsl:stylesheet>