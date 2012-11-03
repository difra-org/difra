<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="cdn_hosts">

		<h2>CDN
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cdn/adm/hostsTitle"/>
		</h2>

		
		<table>
			<tr>
				<td>
					<div class="cdn-status ok"/>
				</td>
				<td>
					<xsl:value-of select="$locale/cdn/adm/legend/ok"/>
				</td>
				<td>
					<div class="cdn-status busy"/>
				</td>
				<td>
					<xsl:value-of select="$locale/cdn/adm/legend/busy"/>
				</td>
				<td>
					<div class="cdn-status fail"/>
				</td>
				<td>
					<xsl:value-of select="$locale/cdn/adm/legend/fail"/>
				</td>
				<td>
					<div class="cdn-status nottested"/>
				</td>
				<td>
					<xsl:value-of select="$locale/cdn/adm/legend/noTest"/>
				</td>
			</tr>
		</table>

		<table style="padding-top: 15px;">
			<tr>
				<th></th>
				<th>
					<xsl:value-of select="$locale/cdn/adm/host"/>
				</th>
				<th>
					<xsl:value-of select="$locale/cdn/adm/lastChecked"/>
				</th>
				<th>
					<xsl:value-of select="$locale/cdn/adm/lastError"/>
				</th>
				<th>
					<xsl:value-of select="$locale/cdn/adm/lastSelected"/>
				</th>
				<th>
					<xsl:value-of select="$locale/cdn/adm/actions"/>
				</th>
			</tr>

			<xsl:for-each select="host">
				<tr>
					<xsl:if test="@id=/root/@cdn_host_id">
						<xsl:attribute name="class">
							<xsl:text>selectedHost</xsl:text>
						</xsl:attribute>
					</xsl:if>
					<td>
						<div class="cdn-status {@status}" />
					</td>
					<td>
						<xsl:value-of select="@host"/>
						<xsl:text>:</xsl:text>
						<xsl:value-of select="@port"/>
					</td>
					<td>
						<xsl:value-of select="@checked"/>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="@failed='0000-00-00 00:00:00'">
								<div style="text-align: center">
									<xsl:text>-</xsl:text>
								</div>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@failed"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="@selected='0000-00-00 00:00:00'">
								<div style="text-align: center">
									<xsl:text>-</xsl:text>
								</div>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@selected"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<a href="/adm/cdn/check/{@id}/" class="action ajaxer">
							<xsl:value-of select="$locale/cdn/adm/checkThis"/>
						</a>
						<a href="/adm/cdn/edit/{@id}/" class="action edit">edit</a>
						<a href="/adm/cdn/delete/{@id}/" class="action delete">delete</a>
					</td>
				</tr>
			</xsl:for-each>
		</table>

		<a href="/adm/cdn/addhost" class="action">
			<xsl:value-of select="$locale/cdn/adm/addHost"/>
		</a>

	</xsl:template>
</xsl:stylesheet>