<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/locales">
		<h2>
			<xsl:value-of select="$locale/adm/locales/title"/>
		</h2>
		<table>
			<tr>
				<th>Язык</th>
				<th>Модуль</th>
				<th>Информация</th>
			</tr>
			<xsl:for-each select="locale">
				<xsl:for-each select="module">
					<tr>
						<xsl:if test="position()=1">
							<td rowspan="{count(../module)}">
								<xsl:value-of select="../@name"/>
							</td>
						</xsl:if>
						<td>
							<xsl:value-of select="@name"/>
						</td>
						<td>
							<xsl:text>Локализовано: </xsl:text>
							<xsl:value-of select="count(item[@usage>0])"/>
							<xsl:text> (</xsl:text>
							<xsl:choose>
								<xsl:when test="count(item[@usage>0])">
									<xsl:value-of select="round( 100  * count(item[@usage>0][@missing=0]) div count(item[@usage>0]) )"/>
									<xsl:text>%</xsl:text>
								</xsl:when>
								<xsl:otherwise>0%</xsl:otherwise>
							</xsl:choose>
							<xsl:text>)</xsl:text>
							<br/>
							<xsl:choose>
								<xsl:when test="count(item[@usage=0])">
									<div>
										<a href="#"
										   onclick="$('#u_{../@name}_{position()}').toggle('fast')"
										   class="dotted">
											<xsl:text>Лишних строк: </xsl:text>
											<xsl:value-of select="count(item[@usage=0])"/>
										</a>
										<div id="u_{../@name}_{position()}" style="display:none">
											<xsl:for-each select="item[@usage=0]">
												<xsl:value-of select="@source"/>
												<xsl:text>: </xsl:text>
												<xsl:value-of select="@xpath"/>
												<br/>
											</xsl:for-each>
										</div>
									</div>
								</xsl:when>
							</xsl:choose>
							<xsl:choose>
								<xsl:when test="count(item[@missing=1])">
									<div>
										<a href="#" onclick="$('#m_{../@name}_{position()}').toggle()" class="dotted">
											<xsl:text>Не хватает строк: </xsl:text>
											<xsl:value-of select="count(item[@missing=1])"/>
										</a>
										<div id="m_{../@name}_{position()}" style="display:none">
											<xsl:for-each select="item[@missing=1]">
												<xsl:value-of select="@source"/>
												<xsl:text>: </xsl:text>
												<xsl:value-of select="@xpath"/>
												<br/>
											</xsl:for-each>
										</div>
									</div>
								</xsl:when>
							</xsl:choose>
						</td>
					</tr>
				</xsl:for-each>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>