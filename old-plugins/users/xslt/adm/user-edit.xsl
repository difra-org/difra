<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="user-edit">

		<h2>
			<a href="/adm/users/list">
				<xsl:value-of select="$locale/users/adm/listTitle"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:choose>
				<xsl:when test="@new">
					<xsl:value-of select="$locale/users/adm/add/title"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$locale/users/adm/edit/title"/>
				</xsl:otherwise>
			</xsl:choose>

		</h2>
		<h3>
			<xsl:value-of select="$locale/users/adm/edit/main"/>
		</h3>

		<form class="ajaxer" action="/adm/users/list/saveuser/{user/@id}">

			<table class="form">
				<colgroup>
					<col style="width: 250px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/email"/>
					</th>
					<td>
						<div class="container">
							<input type="text" class="full-width" name="email" value="{user/@email}" id="email"/>
							<div class="status"/>
						</div>
					</td>
				</tr>
				<xsl:if test="not(@new)">
					<tr>
						<th>
							<label for="changePw" class="checkbox_label">
								<xsl:value-of select="$locale/users/adm/edit/changePassword"/>
							</label>
						</th>
						<td>
							<input type="checkbox" name="change_pw" id="changePw" onchange="changePassEnabler()"/>
						</td>
					</tr>
				</xsl:if>
				<tr>
					<th>
						<xsl:choose>
							<xsl:when test="@new">
								<xsl:value-of select="$locale/auth/password"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$locale/users/adm/edit/newPassword"/>
							</xsl:otherwise>
						</xsl:choose>

					</th>
					<td>
						<div class="container">
							<input type="text" class="full-width" name="password" id="newPw">
								<xsl:if test="not(@new)">
									<xsl:attribute name="disabled">
										<xsl:text>disabled</xsl:text>
									</xsl:attribute>
								</xsl:if>
							</input>
							<div class="status"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="active" class="checkbox_label">
							<xsl:value-of select="$locale/users/adm/edit/activated"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="active" id="active">
							<xsl:if test="user/@active=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="banned" class="checkbox_label">
							<xsl:value-of select="$locale/users/adm/edit/banned"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="banned" id="banned">
							<xsl:if test="user/@banned=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="moderator" class="checkbox_label">
							<xsl:value-of select="$locale/users/adm/moderator"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="moderator" id="moderator">
							<xsl:if test="user/@moderator=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/users/adm/additionals/title"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 250px"/>
					<col/>
					<col style="width: 30px;"/>
				</colgroup>
				<tr class="additionalField">
					<th><xsl:value-of select="$locale/users/adm/additionals/field"/></th>
					<th><xsl:value-of select="$locale/users/adm/additionals/value"/></th>
					<td></td>
				</tr>
				<xsl:for-each select="user/additionals/field">
					<tr class="additionalField">
						<td>
							<input type="text" name="fieldName[]" value="{@name}"/>
						</td>
						<td>
							<input type="text" name="fieldValue[]" class="full-width" value="{@value}"/>
						</td>
						<td>
							<a href="#" class="action delete" onclick="deleteAddtitionalField( this )"/>
						</td>
					</tr>
				</xsl:for-each>
				<tr>
					<td>
						<a href="#" class="action add" onclick="addAdditionalField()"/>
					</td>
					<td></td>
					<td></td>
				</tr>
			</table>

			<div class="form-buttons">
				<input type="submit">
					<xsl:attribute name="value">
						<xsl:choose>
							<xsl:when test="@new">
								<xsl:value-of select="$locale/users/adm/add/add"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$locale/adm/save"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
				</input>
			</div>
		</form>

	</xsl:template>
</xsl:stylesheet>

