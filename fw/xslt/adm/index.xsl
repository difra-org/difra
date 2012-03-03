<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/index">
		<h2>Общая информация</h2>

		<h3>Difra</h3>
		<table class="summary">
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
				<th>Тип кеширования</th>
				<td>
					<xsl:value-of select="stats/cache/@type"/>
				</td>
			</tr>
		</table>
		<h3>Окружение</h3>
		<table class="summary">
			<xsl:for-each select="stats/system/*">
				<tr>
					<xsl:variable name="statName" select="name()"/>
					<th><xsl:value-of select="$locale/adm/stats/server/*[name()=$statName]"/></th>
					<td><xsl:value-of select="./text()"/></td>
				</tr>
			</xsl:for-each>
		</table>
		<h3>Расширения PHP</h3>
		<table class="summary">
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
		<h3>База данных</h3>
		<xsl:choose>
			<xsl:when test="stats/mysql/@ok=1">
				<div class="message">Всё в порядке.</div>
			</xsl:when>
			<xsl:otherwise>
				<div class="message">
					<xsl:value-of disable-output-escaping="yes" select="stats/mysql"/>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>