<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="rss_settings">

		<h2>
			<xsl:value-of select="$locale/rss/adm/settingsTitle"/>
		</h2>

		<form name="rss_settings_form" method="post" action="/adm/settings/rss/savesettings/" class="ajaxer">
			<table class="form">
				<tr>
					<th>
						<label for="onLine">
							<xsl:value-of select="$locale/rss/adm/onLine"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="onLine" id="onLine" value="1">
							<xsl:if test="@onLine=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="cache">
							<xsl:value-of select="$locale/rss/adm/cache"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="cache" id="cache" value="1">
							<xsl:if test="@cache=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/loadImage"/>
						<xsl:choose>
							<xsl:when test="/root/content/rss_settings/@logo=1">
								<br/>
								<img src="/rss/rsslogo.png"/>
							</xsl:when>
							<xsl:otherwise>
								<span class="rssInfo">
									<xsl:value-of select="$locale/rss/adm/noRssLogo"/>
								</span>
							</xsl:otherwise>
						</xsl:choose>

					</th>
					<td>
						<input type="file" name="rsslogo" accept="image/jpeg,image/png,image/gif" />
						<xsl:if test="/root/content/rss_settings/@logo=1">
							<br/>
							<a href="/adm/settings/rss/deletelogo/" class="button ajaxer">
								<xsl:value-of select="$locale/rss/adm/deleteImage"/>
							</a>
						</xsl:if>
					</td>
				</tr>
			</table>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/title"/>
					</th>
					<td>
						<input type="text" name="title" value="{@title}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/link"/>
					</th>
					<td>
						<input type="text" name="link" value="{@link}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/desc"/>
					</th>
					<td>
						<input type="text" name="desc" value="{@description}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/copyright"/>
					</th>
					<td>
						<input type="text" name="copyright" value="{@copyright}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/ttl"/>
					</th>
					<td>
						<input type="number" name="ttl" value="{@ttl}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/rss/adm/size"/>
					</th>
					<td>
						<input type="number" name="size" value="{@size}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="image">
							<xsl:value-of select="$locale/rss/adm/image"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="image" id="image" value="1">
							<xsl:if test="@image=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<input type="submit" class="button" value="{$locale/rss/adm/save}" />

		</form>

	</xsl:template>
</xsl:stylesheet>