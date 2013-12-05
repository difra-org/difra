<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  version="1.0">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/mail">
		<fromtext><xsl:value-of select="/mail/locale/users/mail/from"/></fromtext>

		<subject>
			<xsl:value-of select="/mail/locale/users/mail/recover/subject"/>
			<xsl:value-of select="@siteName"/>
		</subject>
		<text>
			<xsl:value-of select="/mail/locale/users/mail/recover/text1" disable-output-escaping="yes"/>

			<a href="http://{@link}"><xsl:value-of select="@link"/></a>

			<xsl:value-of select="/mail/locale/users/mail/recover/text2" disable-output-escaping="yes"/>
			<xsl:value-of select="@ttl"/>
			<xsl:value-of select="/mail/locale/users/mail/recover/text3" disable-output-escaping="yes"/>
		</text>
	</xsl:template>
</xsl:stylesheet>
