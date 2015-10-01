<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/mail">
		<fromtext>
			<xsl:value-of select="/mail/locale/auth/mail/from"/>
		</fromtext>

		<subject>
			<xsl:value-of select="/mail/locale/auth/mail/registration/subject"/>
		</subject>
		<text>
			<xsl:value-of select="/mail/locale/auth/mail/registration/welcome1"
				      disable-output-escaping="yes"/>
			<xsl:value-of select="@host"/>
			<xsl:value-of select="/mail/locale/auth/mail/registration/welcome2"
				      disable-output-escaping="yes"/>
			<br/>
			<br/>
			<xsl:value-of select="/mail/locale/auth/mail/registration/username"
				      disable-output-escaping="yes"/>
			<xsl:value-of select="@user"/>
			<br/>
			<xsl:value-of select="/mail/locale/auth/mail/registration/password"
				      disable-output-escaping="yes"/>
			<xsl:value-of select="@password"/>
			<br/>
			<br/>
			<xsl:choose>
				<xsl:when test="@confirm='moderate'">
					<xsl:value-of select="/mail/locale/auth/mail/registration/moderate"/>
					<br/>
					<br/>
				</xsl:when>
				<xsl:when test="@confirm='email'">
					<xsl:value-of select="/mail/locale/auth/mail/registration/email1"
						      disable-output-escaping="yes"/>
					<a href="http://{@host}/auth/activate/{@code}">http://<xsl:value-of
						select="@host"/>/auth/activate/<xsl:value-of select="@code"/>
					</a>
					<xsl:value-of select="/mail/locale/auth/mail/registration/email2"
						      disable-output-escaping="yes"/>
					<br/>
					<xsl:value-of select="/mail/locale/auth/mail/registration/email3"
						      disable-output-escaping="yes"/>
					<br/>
					<br/>
				</xsl:when>
			</xsl:choose>
			<xsl:value-of select="/mail/locale/auth/mail/registration/legal1"
				      disable-output-escaping="yes"/>
			<a href="mailto:support@{@host}">support@<xsl:value-of select="@host"/>
			</a>
			<xsl:value-of select="/mail/locale/auth/mail/registration/legal2"
				      disable-output-escaping="yes"/>
		</text>
	</xsl:template>
</xsl:stylesheet>
