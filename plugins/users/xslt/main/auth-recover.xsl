<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="auth-recover" name="auth-recover">

		<div class="authForm">
			<h3><xsl:value-of select="$locale/recover/title"/></h3>

			<div id="recoverForm">
				<form class="ajaxer" action="/auth/recover/send/">

					<label><xsl:value-of select="$locale/auth/email"/></label>

					<div class="container">
						<input type="email" name="email" autofocus="autofocus"/>
						<div class="status"/>
					</div>

					<input type="submit" value="{$locale/recover/recover}"/>
				</form>
			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>
