<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="root/user-view">

		<div class="userView">
			<h3>
				<xsl:value-of select="$locale/users/adm/user"/>
			</h3>

			<table>

				<tr>
					<td>
						<xsl:value-of select="$locale/users/adm/email"/>
						<xsl:text>: </xsl:text>
					</td>
					<td>
						<xsl:value-of select="user/@email"/>
					</td>
				</tr>
				<tr>
					<td>
						<xsl:value-of select="$locale/users/adm/registered"/>
					</td>
					<td>
						<xsl:value-of select="user/@registered"/>
					</td>
				</tr>
				<tr>
					<td>
						<xsl:value-of select="$locale/users/adm/logged"/>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="not(user/@logged='') and not(user/@logged='0000-00-00 00:00:00')">
								<xsl:value-of select="user/@logged"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$locale/users/adm/noLogin"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
				<xsl:if test="user/@moderator=1">
					<tr>
						<td class="flag">
							<xsl:value-of select="$locale/users/adm/thisIsModerator"/>
						</td>
						<td></td>
					</tr>
				</xsl:if>
				<tr>
					<td class="flag">
						<xsl:choose>
							<xsl:when test="user/@banned=1 and user/@active=0">
								<xsl:value-of select="$locale/users/adm/inactive"/>
								<xsl:text>,&#160;</xsl:text>
								<xsl:value-of select="$locale/users/adm/banned"/>
							</xsl:when>
							<xsl:when test="user/@banned=1">
								<xsl:value-of select="$locale/users/adm/banned"/>
							</xsl:when>
							<xsl:when test="user/@active=0">
								<xsl:value-of select="$locale/users/adm/inactive"/>
							</xsl:when>
						</xsl:choose>
					</td>
					<td></td>
				</tr>
			</table>
			<br/><br/>
			<table>
				<xsl:for-each select="user/additionals/field">
					<tr>
						<td>
							<xsl:choose>
								<xsl:when test="@localeName and not(@localeName='')">
									<xsl:value-of select="@localeName"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="@name"/>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:text>: </xsl:text>
						</td>
						<td>
							<xsl:value-of select="@value"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</div>

	</xsl:template>
</xsl:stylesheet>
