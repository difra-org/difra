<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="cdn_edit_host">

		<h2>CDN
			<xsl:text> → </xsl:text>
			<a href="/adm/cdn/hosts/"><xsl:value-of select="$locale/cdn/adm/hostsTitle"/></a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="@host"/>
			<xsl:text>:</xsl:text>
			<xsl:value-of select="@port"/>
		</h2>

		<form action="/adm/cdn/edit/{@id}/" class="ajaxer" method="post">

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/cdn/adm/forms/host"/>
					</th>
					<td>
						<input name="host" type="text" value="{@host}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/cdn/adm/forms/port"/>
					</th>
					<td>
						<input name="port" type="number" value="{@port}"/>
					</td>
				</tr>
			</table>
			<input type="submit" value="{$locale/cdn/adm/saveHost}"/>
		</form>
	</xsl:template>
</xsl:stylesheet>