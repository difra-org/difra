<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  version="1.0">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/mail">
		<fromtext><xsl:value-of select="/mail/locale/users/mail/from"/></fromtext>

		<subject>
			<xsl:value-of select="/mail/locale/users/mail/newuser/subject"/>
			<xsl:value-of select="@host"/>
		</subject>
		<text>

			<xsl:value-of select="/mail/locale/users/mail/newuser/notify"/>
			<br/><br/>
			<xsl:value-of select="/mail/locale/users/mail/newuser/email"/>
			<strong><xsl:value-of select="@email"/></strong>

		</text>
	</xsl:template>
</xsl:stylesheet>
