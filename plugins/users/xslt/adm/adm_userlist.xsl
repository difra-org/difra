<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="userList">
		<h2>
			<xsl:value-of select="$locale/auth/adm/h2-title"/>
		</h2>
		<h3>
			<xsl:value-of select="$locale/auth/adm/h3-userlist"/>
		</h3>
		<xsl:choose>
			<xsl:when test="not(item)">
				<xsl:value-of select="$locale/auth/adm/users-empty"/>
			</xsl:when>
			<xsl:otherwise>
				<table id="userList">
					<tr>
						<th>
							<xsl:value-of select="$locale/auth/adm/id"/>
						</th>
						<th>
							<xsl:value-of select="$locale/auth/adm/email"/>
						</th>
						<th>
							<xsl:value-of select="$locale/auth/adm/registered"/>
						</th>
						<th>
							<xsl:value-of select="$locale/auth/adm/logged"/>
						</th>
						<th>
							<xsl:value-of select="$locale/auth/adm/flags"/>
						</th>
						<th>
							<xsl:value-of select="$locale/auth/adm/actions"/>
						</th>
					</tr>
					<xsl:for-each select="item">
						<tr>
							<td>
								<xsl:value-of select="@id"/>
							</td>
							<td>
								<xsl:value-of select="@email"/>
							</td>
							<td>
								<xsl:value-of select="@registered"/>
							</td>
							<td>
								<xsl:choose>
									<xsl:when test="@logged='0000-00-00 00:00:00'">
										<xsl:text>—</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="@logged"/>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							<td>
								<xsl:choose>
									<xsl:when test="@banned=1 and @active=0">
										<xsl:value-of select="$locale/auth/adm/inactive"/>
										<xsl:text>,&#160;</xsl:text>
										<xsl:value-of select="$locale/auth/adm/banned"/>
									</xsl:when>
									<xsl:when test="@banned=1">
										<xsl:value-of select="$locale/auth/adm/banned"/>
									</xsl:when>
									<xsl:when test="@active=0">
										<xsl:value-of select="$locale/auth/adm/inactive"/>
									</xsl:when>
									<xsl:when test="@moderator=1">
										<xsl:value-of select="$locale/auth/adm/moderator_flag"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>—</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							<td>
								<a href="/adm/users/edit/{@id}" class="action edit">
									<xsl:value-of select="$locale/auth/adm/edit"/>
								</a>
								<xsl:choose>
									<xsl:when test="@banned=1">
										<a href="/adm/users/unban/{@id}" class="action">
											<xsl:value-of select="$locale/auth/adm/unban"/>
										</a>
									</xsl:when>
									<xsl:otherwise>
										<a href="/adm/users/ban/{@id}" class="action">
											<xsl:value-of select="$locale/auth/adm/ban"/>
										</a>
									</xsl:otherwise>
								</xsl:choose>
								<xsl:choose>
									<xsl:when test="@moderator=1">
										<a href="/adm/users/unmoderator/{@id}" class="action">
											<xsl:value-of select="$locale/auth/adm/unModerator"/>
										</a>
									</xsl:when>
									<xsl:otherwise>
										<a href="/adm/users/moderator/{@id}" class="action">
											<xsl:value-of select="$locale/auth/adm/moderator"/>
										</a>
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:otherwise>
		</xsl:choose>

		<xsl:if test="/root/userList/@pages&gt;1">
			<br/>
			<div class="paginator">
				<xsl:call-template name="paginator">
					<xsl:with-param name="link">
						<xsl:value-of select="/root/userList/@link"/>
					</xsl:with-param>
					<xsl:with-param name="pages">
						<xsl:value-of select="/root/userList/@pages"/>
					</xsl:with-param>
					<xsl:with-param name="current">
						<xsl:value-of select="/root/userList/@current"/>
					</xsl:with-param>
				</xsl:call-template>
			</div>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>

