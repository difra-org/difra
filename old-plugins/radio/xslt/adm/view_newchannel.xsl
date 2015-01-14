<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="newchannel-view">
		<h2>
			<a href="/adm/radio/channels">
				<xsl:value-of select="$locale/radio/viewTitle"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/radio/creation"/>
		</h2>

		<span class="air">Air</span><xsl:value-of select="$locale/radio/airHelp"/>&#160;
		<span class="siteView">site</span><xsl:value-of select="$locale/radio/siteHelp"/>

		<form method="post" action="/adm/radio/createchannel" name="saveChannelSettingsForm" class="ajaxer">

			<h3><xsl:value-of select="$locale/radio/mainParams"/></h3>
			<table class="form">
				<tr>
					<th><xsl:value-of select="$locale/radio/channelName"/>
						<span class="separator"/>
						<span class="air">Air</span>&#160;
						<span class="siteView">site</span>
					</th>
					<td>
						<input type="text" name="name" />
					</td>
				</tr>
				<tr>
					<th><xsl:value-of select="$locale/radio/mountPoint"/></th>
					<td>
						<div class="container">
							<div class="invalid" style="display:none">
								<div class="invalid-text"></div>
							</div>
							<input type="text" name="mount" />
							<span class="gray small">
								<xsl:value-of select="$locale/radio/mountHelp"/>
							</span>
						</div>
					</td>
				</tr>

				<tr>
					<th><xsl:value-of select="$locale/radio/desc"/>
						<span class="separator"/>
						<span class="siteView">site</span>
					</th>
					<td>
						<input type="text" name="siteDescription" />
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/airDesc"/>
						<span class="separator"/>
						<span class="air">Air</span>
					</th>
					<td>
						<input type="text" name="description"/>
					</td>
				</tr>
				<tr>
					<th><xsl:value-of select="$locale/radio/genre"/>
						<span class="separator"/>
						<span class="air">Air</span>
					</th>
					<td>
						<input type="text" name="genre"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/url"/>
						<span class="separator"/>
						<span class="air">Air</span>
					</th>
					<td>
						<input type="text" name="url" value="http://{/root/@mainhost}"/>
					</td>
				</tr>
			</table>

			<h3><xsl:value-of select="$locale/radio/channelSettings"/></h3>

			<table class="form">
				
				<tr>
					<th><xsl:value-of select="$locale/radio/bitrate"/></th>
					<td class="min">
						<div class="container">
							<div class="invalid" style="display:none">
								<div class="invalid-text"></div>
							</div>
							<input type="text" name="bitrate" value="192"/>
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
							<input type="text" name="samplerate" value="44100"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/hostname"/>
					</th>
					<td>
						<input type="text" name="hostname" value="localhost"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/port"/>
					</th>
					<td class="min">
						<input type="text" name="port" value="8000"/>
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
							<input type="text" name="password" />
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/radio/reencode"/>
					</th>
					<td>
						<input type="radio" name="reencode" value="1" checked="checked"/>
						<label>
							<xsl:value-of select="$locale/radio/yes"/>
						</label>
						<input type="radio" name="reencode" value="0"/>
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
						<input type="radio" name="debug" value="1" checked="checked"/>
						<label>
							<xsl:value-of select="$locale/radio/yes"/>
						</label>
						<input type="radio" name="debug" value="0"/>
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
						<input type="radio" name="onLine" value="1"/>
						<label>
							<xsl:value-of select="$locale/radio/yes"/>
						</label>
						<input type="radio" name="onLine" value="0" checked="checked"/>
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
						<input type="text" name="minSongInQuery" value="60"/>
						<xsl:value-of select="$locale/radio/min"/>
					</td>
				</tr>
				<tr>
					<th>Время повтора исполнителя в ротации</th>
					<td class="min">
						<input type="text" name="minArtistInQuery" value="30"/><xsl:value-of select="$locale/radio/min"/>
					</td>
				</tr>
				<tr>
					<th>Кол-во треков в плей-листе. Кол-во итераций генерации при авторотации:</th>
					<td class="min">
						<input type="text" name="tracksCount" value="3"/>
					</td>
				</tr>
			</table>

			<input type="submit" value="{$locale/radio/createChannel}" class="button"/>
		</form>

	</xsl:template>
</xsl:stylesheet>