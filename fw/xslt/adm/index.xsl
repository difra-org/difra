<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/index">
		<h2><xsl:value-of select="$locale/adm/stats/h2"/></h2>

		<h3>Difra</h3>
		<table class="summary">
			<tr>
				<th><xsl:value-of select="$locale/adm/stats/summary/platform-version"/></th>
				<td><xsl:value-of select="stats/difra/@version"/></td>
			</tr>
			<tr>
				<th><xsl:value-of select="$locale/adm/stats/summary/loaded-plugins"/></th>
				<td>
					<xsl:value-of select="stats/plugins/@loaded"/>
				</td>
			</tr>
			<xsl:if test="not(stats/plugins/@disabled='')">
				<tr>
					<th><xsl:value-of select="$locale/adm/stats/summary/disabled-plugins"/></th>
					<td style="color:red">
						<xsl:value-of select="stats/plugins/@disabled"/>
					</td>
				</tr>
			</xsl:if>
			<tr>
				<th><xsl:value-of select="$locale/adm/stats/summary/cache-type"/></th>
				<td>
					<xsl:value-of select="stats/cache/@type"/>
				</td>
			</tr>
		</table>
		<h3><xsl:value-of select="$locale/adm/stats/server/title"/></h3>
		<table class="summary">
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/server/webserver"/>
				</th>
				<td>
					<xsl:value-of select="stats/system/webserver"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/server/phpversion"/>
				</th>
				<td>
					<xsl:value-of select="stats/system/phpversion"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/stats/permissions"/>
				</th>
				<td>
					<xsl:choose>
						<xsl:when test="stats/permissions/@*">
							<xsl:for-each select="stats/permissions/@*">
								<div style="color:red">
									<xsl:value-of select="."/>
								</div>
							</xsl:for-each>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="$locale/adm/stats/permissions-ok"/>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
		<h3><xsl:value-of select="$locale/adm/stats/extensions/title"/></h3>
		<table class="summary">
			<tr>
				<th><xsl:value-of select="$locale/adm/stats/extensions/required-extensions"/></th>
				<td>
					<xsl:choose>
						<xsl:when test="not(stats/extensions/@ok='')">
							<xsl:value-of select="stats/extensions/@ok"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>—</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
			<xsl:if test="not(stats/extensions/@required='')">
				<tr>
					<th><xsl:value-of select="$locale/adm/stats/extensions/missing-extensions"/></th>
					<td style="color:red">
						<xsl:value-of select="stats/extensions/@required"/>
					</td>
				</tr>
			</xsl:if>
			<tr>
				<th><xsl:value-of select="$locale/adm/stats/extensions/extra-extensions"/></th>
				<td>
					<xsl:choose>
						<xsl:when test="not(stats/extensions/@extra='')">
							<xsl:value-of select="stats/extensions/@extra"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>—</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</td>
			</tr>
		</table>
		<h3><xsl:value-of select="$locale/adm/stats/database/title"/></h3>
		<xsl:choose>
			<xsl:when test="stats/mysql/@ok=1">
				<div class="message"><xsl:value-of select="$locale/adm/stats/database/status-ok"/></div>
			</xsl:when>
			<xsl:otherwise>
				<div class="message">
					<xsl:value-of disable-output-escaping="yes" select="stats/mysql"/>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>