<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="root/user-recovery-change">
		<div class="authForm">
			<h2>
				<xsl:value-of select="$locale/recover/title"/>
			</h2>

			<div class="message">

				<xsl:choose>
					<xsl:when test="@error and not(@error='')">

						<xsl:variable name="errorName" select="@error"/>
						<xsl:value-of select="$locale/recover/errors/*[name()=$errorName]/text()"/>

					</xsl:when>
					<xsl:otherwise>
						<form class="ajaxer" action="/auth/recover/change/{@code}">

							<label><xsl:value-of select="$locale/auth/password"/></label>
							<div class="container">
								<input type="password" name="password" />
								<div class="status"/>
							</div>

							<label><xsl:value-of select="$locale/auth/password2"/></label>
							<div class="container">
								<input type="password" name="password2" />
								<div class="status"/>
							</div>

							<input type="submit" value="{$locale/recover/recover}" />
						</form>
					</xsl:otherwise>
				</xsl:choose>

			</div>
		</div>
	</xsl:template>

</xsl:stylesheet>