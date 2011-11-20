<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/index">
		<h2>Статистика</h2>
		<table class="twoColumns">
			<tr>
				<td>
					<h3>Difra</h3>
					<table>
						<tr>
							<th>Версия платформы</th>
							<td><xsl:value-of select="stats/difra/@version"/></td>
						</tr>
						<tr>
							<th>Загруженные плагины</th>
							<td>
								<xsl:value-of select="stats/plugins/@loaded"/>
							</td>
						</tr>
						<xsl:if test="not(stats/plugins/@disabled='')">
							<tr>
								<th>Отключенные плагины</th>
								<td style="color:red">
									<xsl:value-of select="stats/plugins/@disabled"/>
								</td>
							</tr>
						</xsl:if>
						<tr>
							<td colspan="2">
								<h4>Кеширование</h4>
							</td>
						</tr>
						<tr>
							<td>Тип кеширования</td>
							<td>
								<xsl:value-of select="stats/cache/@type"/>
							</td>
						</tr>
					</table>
				</td>
				<td>
					<h3>Окружение</h3>
					<table>
						<tr>
							<td colspan="2">
								<h4>Система</h4>
							</td>
						</tr>
						<xsl:for-each select="stats/system/*">
							<tr>
								<xsl:variable name="statName" select="name()"/>
								<th><xsl:value-of select="$locale/adm/stats/server/*[name()=$statName]"/></th>
								<td><xsl:value-of select="./text()"/></td>
							</tr>
						</xsl:for-each>
						<tr>
							<td colspan="2">
								<h4>Расширения PHP</h4>
							</td>
						</tr>
						<tr>
							<th>Необходимые расширения</th>
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
								<th>Требуются расширения</th>
								<td style="color:red">
									<xsl:value-of select="stats/extensions/@required"/>
								</td>
							</tr>
						</xsl:if>
						<tr>
							<th>Другие расширения</th>
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
				</td>
			</tr>
		</table>
	</xsl:template>
</xsl:stylesheet>