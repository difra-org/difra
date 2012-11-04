<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="settings-channel">
		<h2>
			<a href="/adm/radio/channels">
				<xsl:value-of select="$locale/radio/viewTitle"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/radio/edit"/>
			<xsl:text>«</xsl:text>
			<xsl:value-of select="/root/settings-channel/name"/>
			<xsl:text>»</xsl:text>
		</h2>

		<span class="air">air</span><xsl:value-of select="$locale/radio/airHelp"/>&#160;
		<span class="siteView">site</span><xsl:value-of select="$locale/radio/siteHelp"/>

		<form method="post" action="/adm/radio/savechannelsettings/{/root/settings-channel/mount}/" name="saveChannelSettingsForm" class="ajaxer">

			<h3>
				<xsl:value-of select="$locale/radio/mainParams"/>
			</h3>
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/channelName"/>
						<span class="separator"/>
						<span class="air">air</span>
						<span class="siteView">site</span>
					</th>
					<td>
						<input type="text" name="name" value="{/root/settings-channel/name}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/mountPoint"/>
					</th>
					<td>
						<div class="container">
							<div class="invalid" style="display:none">
								<div class="invalid-text"></div>
							</div>
							<input type="text" name="mount" value="{/root/settings-channel/mount}"/>
							<span class="gray small">
								<xsl:value-of select="$locale/radio/mountHelp"/>
							</span>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/desc"/>
						<span class="separator"/>
						<span class="siteView">site</span>
					</th>
					<td>
						<input type="text" name="siteDescription" value="{/root/settings-channel/siteDescription}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/airDesc"/>
						<span class="separator"/>
						<span class="air">air</span>
					</th>
					<td>
						<input type="text" name="description" value="{/root/settings-channel/description}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/genre"/>
						<span class="separator"/>
						<span class="air">air</span>
					</th>
					<td>
						<input type="text" name="genre" value="{/root/settings-channel/genre}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/url"/>
						<span class="separator"/>
						<span class="air">air</span>
					</th>
					<td>
						<input type="text" name="url" value="{/root/settings-channel/url}"/>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/radio/channelSettings"/>
			</h3>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/bitrate"/>
					</th>
					<td class="min">
						<div class="container">
							<div class="invalid" style="display:none">
								<div class="invalid-text"></div>
							</div>
							<input type="text" name="bitrate" value="{/root/settings-channel/bitrate}"/>
							<xsl:value-of select="$locale/radio/kbps"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/sampleRate"/>
					</th>
					<td class="min">
						<div class="container">
							<div class="invalid" style="display:none">
								<div class="invalid-text"></div>
							</div>
							<input type="text" name="samplerate" value="{/root/settings-channel/samplerate}"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/hostname"/>
					</th>
					<td>
						<input type="text" name="hostname" value="{/root/settings-channel/hostname}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/port"/>
					</th>
					<td class="min">
						<input type="text" name="port" value="{/root/settings-channel/port}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/password"/>
					</th>
					<td>
						<div class="container">
							<div class="invalid" style="display:none">
								<div class="invalid-text"></div>
							</div>
							<input type="text" name="password" value="{/root/settings-channel/password}"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/reencode"/>
					</th>
					<td>
						<input type="radio" name="reencode" value="1">
							<xsl:if test="/root/settings-channel/reencode=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
						<label>
							<xsl:value-of select="$locale/radio/yes"/>
						</label>
						<input type="radio" name="reencode" value="0">
							<xsl:if test="not(/root/settings-channel/reencode=1)">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
						<label>
							<xsl:value-of select="$locale/radio/no"/>
						</label>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/radio/debug"/>
					</th>
					<td>
						<input type="radio" name="debug" value="1">
							<xsl:if test="/root/settings-channel/debug=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
						<label>
							<xsl:value-of select="$locale/radio/yes"/>
						</label>
						<input type="radio" name="debug" value="0">
							<xsl:if test="not(/root/settings-channel/debug=1)">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
						<label>
							<xsl:value-of select="$locale/radio/no"/>
						</label>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/onLine"/>
					</th>
					<td>
						<input type="radio" name="onLine" value="1">
							<xsl:if test="/root/settings-channel/onLine=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
						<label>
							<xsl:value-of select="$locale/radio/yes"/>
						</label>
						<input type="radio" name="onLine" value="0">
							<xsl:if test="not(/root/settings-channel/onLine=1)">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
						<label>
							<xsl:value-of select="$locale/radio/no"/>
						</label>
					</td>
				</tr>
			</table>

			<h3><xsl:value-of select="$locale/radio/rotationSettings"/></h3>
			<table class="form">

				<tr>
					<th>Время повтора трека в ротации</th>
					<td class="min">
						<input type="text" name="minSongInQuery" value="{/root/settings-channel/minSongInQuery}"/>
						<xsl:value-of select="$locale/radio/min"/>
					</td>
				</tr>
				<tr>
					<th>Время повтора исполнителя в ротации</th>
					<td class="min">
						<input type="text" name="minArtistInQuery" value="{/root/settings-channel/minArtistInQuery}"/>
						<xsl:value-of select="$locale/radio/min"/>
					</td>
				</tr>
				<tr>
					<th>Кол-во треков в плей-листе. Кол-во итераций генерации при авторотации</th>
					<td class="min">
						<input type="text" name="tracksCount" value="{/root/settings-channel/tracksCount}"/>
					</td>
				</tr>

			</table>

			<input type="submit" value="{$locale/radio/saveChannel}" class="button"/>
		</form>

	</xsl:template>
</xsl:stylesheet>