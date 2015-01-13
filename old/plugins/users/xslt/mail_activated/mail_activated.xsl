<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  version="1.0">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/mail">
		<fromtext><xsl:value-of select="/mail/locale/users/mail/from"/></fromtext>

		<subject>
			<xsl:value-of select="/mail/locale/users/mail/activated/subject"/>
			<xsl:value-of select="@host"/>
		</subject>
		<text>

			<xsl:value-of select="/mail/locale/users/mail/activated/activated"/>
			<br/>
			<xsl:value-of select="/mail/locale/users/mail/activated/welcome"/>
			<a href="http://{@host}">
				<xsl:value-of select="@host"/>
			</a>
			<br/>
			<xsl:value-of select="/mail/locale/users/mail/activated/login"/>
			<br/>
			<br/>
			<xsl:value-of select="/mail/locale/users/mail/activation/legal1" disable-output-escaping="yes"/>
			<a href="mailto:support@{@host}">support@<xsl:value-of select="@host"/></a>
			<xsl:text>.</xsl:text>
		</text>
	</xsl:template>
</xsl:stylesheet>
