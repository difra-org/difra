<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="user-password" name="user-password">

		<div class="authForm">
			<h3><xsl:value-of select="$locale/changePassword/title"/></h3>

			<div id="passwordChange">
				<form class="ajaxer" action="/auth/password/">

					<label><xsl:value-of select="$locale/changePassword/old"/></label>
					<div class="container">
						<input type="password" name="old" />
						<div class="status"/>
					</div>

					<label><xsl:value-of select="$locale/changePassword/newPassword"/></label>
					<div class="container">
						<input type="password" name="password"/>
						<div class="status"/>
					</div>

					<label><xsl:value-of select="$locale/auth/password2"/></label>
					<div class="container">
						<input type="password" name="password2"/>
						<div class="status"/>
					</div>

					<input type="submit" value="{$locale/changePassword/change}"/>
				</form>
			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>
