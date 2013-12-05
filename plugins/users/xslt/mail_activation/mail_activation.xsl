<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"  version="1.0">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/mail">
		<fromtext><xsl:value-of select="/mail/locale/users/mail/from"/></fromtext>

		<subject><xsl:value-of select="/mail/locale/users/mail/activation/subject"/></subject>
		<text>
			<xsl:value-of select="/mail/locale/users/mail/activation/welcome1" disable-output-escaping="yes"/>
			<xsl:value-of select="@host"/>
			<xsl:text>.</xsl:text>
			<br/>
			<br/>
			<xsl:value-of select="/mail/locale/users/mail/activation/username" disable-output-escaping="yes"/>
			<xsl:value-of select="@email"/>
			<br/>
			<xsl:value-of select="/mail/locale/users/mail/activation/password" disable-output-escaping="yes"/>
			<xsl:value-of select="@password"/>
			<br/>
			<br/>
			<xsl:choose>
				<xsl:when test="@confirm='manual'">
					<xsl:value-of select="/mail/locale/users/mail/activation/manual"/>
					<br/>
					<br/>
				</xsl:when>
				<xsl:when test="@confirm='email'">

					<xsl:value-of select="/mail/locale/users/mail/activation/email1" disable-output-escaping="yes"/>
					<a href="http://{@link}"><xsl:value-of select="@link"/></a>
					<xsl:text>.</xsl:text>

					<br/>
					<xsl:value-of select="/mail/locale/users/mail/activation/email3" />
					<br/>
					<br/>
				</xsl:when>
			</xsl:choose>

			<xsl:value-of select="/mail/locale/users/mail/activation/legal1" disable-output-escaping="yes"/>
			<a href="mailto:support@{@host}">support@<xsl:value-of select="@host"/></a>
			<xsl:text>.</xsl:text>
		</text>
	</xsl:template>
</xsl:stylesheet>
