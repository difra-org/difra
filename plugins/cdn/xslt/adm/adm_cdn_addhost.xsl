<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="cdn_add_host">

		<h2>CDN
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cdn/adm/addTitle"/>
		</h2>

		<form action="/adm/cdn/addhost" class="ajaxer" method="post">
			
			<table class="form">
				<tr>
					<th><xsl:value-of select="$locale/cdn/adm/forms/host"/></th>
					<td><input name="host" type="text" /></td>
				</tr>
				<tr>
					<th><xsl:value-of select="$locale/cdn/adm/forms/port"/></th>
					<td><input name="port" type="number"/></td>
				</tr>
			</table>
			<input type="submit" value="{$locale/cdn/adm/addHost}" />
		</form>
	</xsl:template>
</xsl:stylesheet>