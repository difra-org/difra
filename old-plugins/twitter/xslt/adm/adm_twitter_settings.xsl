<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="twitterSettings">

		<h2>
			<xsl:value-of select="$locale/twitter/adm/oAuth/settingsTitle"/>
		</h2>

		<form name="oauth_settings_form" method="post" action="/adm/settings/twitter/savesettings/" class="ajaxer">

			<table class="form">
				<colgroup>
					<col style="width: 250px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<label for="postToTwitter">
							<xsl:value-of select="$locale/twitter/adm/oAuth/onoff"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="postToTwitter" id="postToTwitter">
							<xsl:if test="@postToTwitter and @postToTwitter=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/twitter/adm/oAuth/consumerKey"/>
					</th>
					<td>
						<input type="text" name="consumerKey" class="full-width" value="{@consumerKey}" />
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/twitter/adm/oAuth/consumerSecret"/>
					</th>
					<td>
						<input type="text" name="consumerSecret" class="full-width" value="{@consumerSecret}" />
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/twitter/adm/oAuth/oAuthtoken"/>
					</th>
					<td>
						<input type="text" name="oauthToken" class="full-width" value="{@accessToken}" />
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/twitter/adm/oAuth/oAuthsecret"/>
					</th>
					<td>
						<input type="text" name="oauthSecret" class="full-width" value="{@accessTokenSecret}" />
					</td>
				</tr>
			</table>

			<input type="submit" value="{$locale/adm/save}"/>

		</form>

	</xsl:template>
</xsl:stylesheet>