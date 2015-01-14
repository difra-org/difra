<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="cdn_settings">

		<h2>CDN
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cdn/adm/settingsTitle"/>
		</h2>


		<form action="/adm/cdn/savesettings/" class="ajaxer" method="post">

			<h3><xsl:value-of select="$locale/cdn/adm/basicSettings"/></h3>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/cdn/adm/hostTimeOut"/>
					</th>
					<td>
						<input type="number" name="timeout" value="{@timeout}" />
					</td>

				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/cdn/adm/cacheTime"/>
					</th>
					<td>
						<input type="number" name="cachetime"  value="{@cachetime}"/>
					</td>

				</tr>
			</table>

			<h3><xsl:value-of select="$locale/cdn/adm/filterSettings"/></h3>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/cdn/adm/failTime"/>
					</th>
					<td>
						<input type="number" name="failtime" value="{@failtime}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/cdn/adm/selectTime"/>
					</th>
					<td>
						<input type="number" name="selecttime" value="{@selecttime}"/>
					</td>
				</tr>
			</table>

			<input type="submit" value="{$locale/cdn/adm/saveSettings}" />
		</form>

	</xsl:template>
</xsl:stylesheet>