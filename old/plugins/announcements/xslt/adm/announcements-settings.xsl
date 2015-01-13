<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsSettings">

		<h2>
			<xsl:value-of select="$locale/announcements/adm/settings"/>
		</h2>

		<form method="post" action="/adm/announcements/settings/save/" class="ajaxer">
			<h3>
				<xsl:value-of select="$locale/announcements/adm/main_settings"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 420px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/maxPerUser"/>
					</th>
					<td>
						<input name="maxPerUser" type="number" value="{@maxPerUser}"/>
						<div class="small grey">
							<xsl:value-of select="$locale/announcements/adm/zeroValueHint"/>
						</div>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/maxPerGroup"/>
					</th>
					<td>
						<input name="maxPerGroup" type="number" value="{@maxPerGroup}"/>
						<div class="small grey">
							<xsl:value-of select="$locale/announcements/adm/zeroValueHint"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/perPage"/>
					</th>
					<td>
						<input name="perPage" type="number" value="{@perPage}"/>
					</td>
				</tr>
			</table>
			<h3>
				<xsl:value-of select="$locale/announcements/adm/imageSize"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 420px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/width"/>
					</th>
					<td>
						<input name="width" type="number" value="{@width}"/>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/height"/>
					</th>
					<td>
						<input name="height" type="number" value="{@height}"/>
					</td>
				</tr>
			</table>
			<h3>
				<xsl:value-of select="$locale/announcements/adm/imageBigSize"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 420px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/width"/>
					</th>
					<td>
						<input name="bigWidth" type="number" value="{@bigWidth}"/>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/height"/>
					</th>
					<td>
						<input name="bigHeight" type="number" value="{@bigHeight}"/>
					</td>
				</tr>
			</table>
			<div class="form-buttons">
				<input type="submit" value="{$locale/adm/save}"/>
			</div>
		</form>

	</xsl:template>
</xsl:stylesheet>