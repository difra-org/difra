<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="PortfolioSettings">
		<h2>
			<a href="/adm/content/portfolio">
				<xsl:value-of select="$locale/portfolio/adm/portfolio"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/portfolio/adm/settings/title"/>
		</h2>

		<form class="ajaxer" action="/adm/settings/portfolio/save/">

			<table class="form">
				<th>
					<xsl:value-of select="$locale/portfolio/adm/settings/image-sizes" disable-output-escaping="yes"/>
				</th>
				<td>
					<textarea rows="7" cols="25" name="imgSizes">
						<xsl:value-of select="@imgSizes"/>
					</textarea>
					<div class="small gray">
						<xsl:value-of select="$locale/portfolio/adm/settings/image-sizes-info" disable-output-escaping="yes"/>
					</div>
				</td>
			</table>

			<input type="submit" value="{$locale/adm/save}" />
		</form>

	</xsl:template>
</xsl:stylesheet>