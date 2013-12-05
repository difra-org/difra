<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="auth-form">

		<div class="authForm" id="auth-form">
			<h3><xsl:value-of select="$locale/auth/title"/></h3>

			<form class="ajaxer" action="/auth/login/">

				<label>
					<xsl:value-of select="$locale/auth/email"/>
				</label>

				<div class="container">
					<input type="email" name="email" autofocus="autofocus"/>
					<div class="status"/>
				</div>

				<label>
					<xsl:value-of select="$locale/auth/password"/>
				</label>

				<div class="container">
					<input type="password" name="password"/>
					<div class="status"/>
				</div>

				<xsl:if test="@showCapcha and @showCapcha=1">
					<div class="container capchaView">
						<label>
							<xsl:value-of select="$locale/auth/capcha"/>
						</label>
						<img src="/capcha" />
						<input type="text" name="capcha" />
					</div>
				</xsl:if>

				<label for="rememberMe">
					<input type="checkbox" name="rememberMe" value="1" id="rememberMe"/>
					<xsl:value-of select="$locale/auth/rememberMe"/>
				</label>

				<a href="/registration/" onclick="ajaxer.close(this);" class="regLink">
					<xsl:value-of select="$locale/auth/noLogin"/>
				</a>

				<input type="submit" value="{$locale/auth/enter}"/>
			</form>
		</div>

	</xsl:template>
</xsl:stylesheet>
