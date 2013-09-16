<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsLocationsEdit">

		<h2>
			<a href="/adm/announcements/">
				<xsl:value-of select="$locale/announcements/adm/announcements"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/announcements/locations/">
				<xsl:value-of select="$locale/announcements/adm/locations/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="@name"/>
		</h2>

		<form class="ajaxer" method="post" action="/adm/announcements/locations/save">

			<input type="hidden" name="id" value="{@id}"/>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/locations/addNewTitle"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 300px;"/>
					<col/>
				</colgroup>

				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/locations/name"/>
					</th>
					<td>
						<input type="text" name="name" value="{@name}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/locations/www"/>
					</th>
					<td>
						<input type="text" name="url" value="{@url}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/locations/address"/>
					</th>
					<td>
						<input type="text" name="address" value="{@address}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/locations/phone"/>
					</th>
					<td>
						<input type="text" name="phone" value="{@phone}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/locations/info"/>
					</th>
					<td>
						<input type="text" name="info" value="{@info}" class="full-width"/>
					</td>
				</tr>
			</table>
			<input type="submit" value="{$locale/adm/save}"/>
		</form>

	</xsl:template>
</xsl:stylesheet>