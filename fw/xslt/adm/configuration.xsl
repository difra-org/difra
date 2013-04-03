<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="/root/configuration">
		<h2>
			<xsl:value-of select="$locale/adm/config/title"/>
		</h2>
		<h3>
			<xsl:value-of select="$locale/adm/config/current"/>
		</h3>
		<textarea disabled="disabled" cols="80" rows="12">
			<xsl:value-of select="@current"/>
		</textarea>
		<hr/>
		<h3>
			<xsl:value-of select="$locale/adm/config/diff"/>
		</h3>
		<textarea rows="12" cols="80" disabled="disabled">
			<xsl:value-of select="@diff"/>
		</textarea>
		<br/>
		<a href="/adm/config/reset" class="ajaxer">
			<xsl:value-of select="$locale/adm/config/reset"/>
		</a>
	</xsl:template>
</xsl:stylesheet>