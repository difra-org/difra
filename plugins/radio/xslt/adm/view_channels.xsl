<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="view-channels">
		<h2>
			<xsl:value-of select="$locale/radio/viewTitle"/>
		</h2>
		<a href="/adm/radio/newchannel/" class="button">
			<xsl:value-of select="$locale/radio/newChannel"/>
		</a>
		<h3>
			Список радиоканалов
		</h3>
		<table>
			<tr>
				<th>
					<xsl:value-of select="$locale/radio/channelTitle"/>
				</th>
				<th>
					<xsl:value-of select="$locale/radio/trackCount"/>
				</th>
				<th>
					<xsl:value-of select="$locale/radio/capacity"/>
				</th>
				<th>
					<xsl:value-of select="$locale/radio/actions"/>
				</th>
			</tr>
			<xsl:for-each select="/root/channels/channel">
				<tr>
					<td>
						<xsl:value-of select="@name"/>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="@emptyChannel=1">
								—
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@track_count"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="@emptyChannel=1">
								—
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@duration"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<a href="/adm/radio/channelsettings/{@name}/" class="action edit"></a>
						<a href="/adm/radio/playlist/{@name}/" class="action"><xsl:value-of select="$locale/radio/playList"/></a>
						<a href="/adm/radio/channeldelete/{@name}/" class="action delete ajaxer"></a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>

