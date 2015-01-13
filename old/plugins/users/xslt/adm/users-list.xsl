<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="users-list">
		<h2>
			<xsl:value-of select="$locale/users/adm/listTitle"/>
		</h2>

		<xsl:call-template name="users-filter"/>
		<xsl:call-template name="users-sort"/>

		<xsl:choose>
			<xsl:when test="UsersUserList/UsersUser">

				<table class="userList">
					<colgroup>
						<col style="width: 5px"/>
						<col/>
						<col/>
					</colgroup>
					<tr>
						<th><xsl:value-of select="$locale/users/adm/id"/></th>
						<th><xsl:value-of select="$locale/users/adm/email"/></th>
						<th><xsl:value-of select="$locale/users/adm/flags"/></th>
						<th class="centerTable"><xsl:value-of select="$locale/users/adm/moderator"/></th>
						<th></th>
					</tr>

					<xsl:for-each select="UsersUserList/UsersUser">
						<tr>
							<td>
								<xsl:value-of select="@id"/>
							</td>
							<td class="userInfo">
								<xsl:value-of select="@email"/>
								<span class="small grey">
									<xsl:value-of select="$locale/users/adm/registered"/>
									<xsl:value-of select="@registered"/>
								</span>
								<span class="small grey">
									<xsl:value-of select="$locale/users/adm/logged"/>
									<xsl:choose>
										<xsl:when test="not(@logged='')">
											<xsl:value-of select="@logged"/>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="$locale/users/adm/noLogin"/>
										</xsl:otherwise>
									</xsl:choose>
								</span>
							</td>
							<td>
								<xsl:choose>
									<xsl:when test="@banned=1 and @active=0">
										<xsl:value-of select="$locale/users/adm/inactive"/>
										<xsl:text>,&#160;</xsl:text>
										<xsl:value-of select="$locale/users/adm/banned"/>
									</xsl:when>
									<xsl:when test="@banned=1">
										<xsl:value-of select="$locale/users/adm/banned"/>
									</xsl:when>
									<xsl:when test="@active=0">
										<xsl:value-of select="$locale/users/adm/inactive"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>—</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							<td class="centerTable">
								<xsl:if test="@moderator=1">
									<div class="action checked"/>
								</xsl:if>
							</td>
							<td>
								<a href="/adm/users/list/view/{@id}" class="action view ajaxer"/>

								<xsl:if test="@active=0">
									<a href="/adm/users/list/activate/{@id}" class="button ajaxer">
										<xsl:value-of select="$locale/users/adm/actions/active"/>
									</a>
								</xsl:if>

								<xsl:choose>
									<xsl:when test="@banned=1">
										<a href="/adm/users/list/unban/{@id}"
										   class="button ajaxer">
											<xsl:value-of select="$locale/users/adm/actions/unban"/>
										</a>
									</xsl:when>
									<xsl:otherwise>
										<a href="/adm/users/list/ban/{@id}"
										   class="button ajaxer">
											<xsl:value-of select="$locale/users/adm/actions/ban"/>
										</a>
									</xsl:otherwise>
								</xsl:choose>
								<xsl:choose>
									<xsl:when test="@moderator=1">
										<a href="/adm/users/list/unmoderator/{@id}"
										   class="button ajaxer">
											<xsl:value-of select="$locale/users/adm/actions/unModerator"/>
										</a>
									</xsl:when>
									<xsl:otherwise>
										<a href="/adm/users/list/moderator/{@id}"
										   class="button ajaxer">
											<xsl:value-of select="$locale/users/adm/actions/moderator"/>
										</a>
									</xsl:otherwise>
								</xsl:choose>
								<a href="/adm/users/list/edit/{@id}" class="action edit"/>
							</td>
						</tr>
					</xsl:for-each>
					<tr>
						<td></td>
						<td>
							<a href="/adm/users/list/add" class="action add" title="{$locale/users/adm/addUserLink}"/>
						</td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</table>

				<xsl:apply-templates match="paginator"/>

			</xsl:when>
			<xsl:otherwise>
				<h3>
					<xsl:value-of select="$locale/users/adm/noUsers"/>
				</h3>

				<a href="/adm/users/list/add">
					<xsl:value-of select="$locale/users/adm/addUserLink"/>
				</a>

			</xsl:otherwise>
		</xsl:choose>

	</xsl:template>
</xsl:stylesheet>

