<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-settings">
		<h2><xsl:value-of select="$locale/settings/title"/></h2>
		<form action="/adm/portfolio/savesettings/" name="loadwork" id="loadWork" enctype="multipart/form-data" method="post" class="ajaxer">
			<h3>
				<xsl:value-of select="$locale/settings/labels/main"/>
			</h3>
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/settings/labels/maxwidth"/>
					</th>
					<td>
						<input type="text" name="maxWidth" id="maxWidth" value="{maxWidth}" />
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/settings/labels/maxheight"/>
					</th>
					<td>
						<input type="text" name="maxHeight" id="maxHeight" value="{maxHeight}"/>
					</td>
				</tr>
			</table>
			<h3>
				<xsl:value-of select="$locale/settings/labels/thumb"/>
			</h3>
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/settings/labels/maxwidth"/>
					</th>
					<td>
						<input type="text" name="thumb_maxWidth" id="thumb_maxWidth" value="{thumb_maxWidth}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/settings/labels/maxheight"/>
					</th>
					<td>
						<input type="text" name="thumb_maxHeight" id="thumb_maxHeight" value="{thumb_maxHeight}"/>
					</td>
				</tr>
			</table>
			<input type="submit" id="sendWork" value="{$locale/settings/labels/save}" />
		</form>

	</xsl:template>
</xsl:stylesheet>

