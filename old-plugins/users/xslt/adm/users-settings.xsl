<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="users-settings">

		<h2>
			<a href="/adm/users/list">
				<xsl:value-of select="$locale/users/adm/listTitle"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/users/adm/settings/title"/>
		</h2>

		<form class="ajaxer" action="/adm/users/settings/save">

			<h3>
				Настройки активации
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 480px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/activeType"/>
					</th>
					<td>
						<select name="activeType">
							<option value="manual">
								<xsl:if test="@activeType='manual'">
									<xsl:attribute name="selected">
										<xsl:text>selected</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="$locale/users/adm/settings/manual"/>
							</option>
							<option value="email">
								<xsl:if test="@activeType='email'">
									<xsl:attribute name="selected">
										<xsl:text>selected</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="$locale/users/adm/settings/email"/>
							</option>
							<option value="no">
								<xsl:if test="@activeType='no'">
									<xsl:attribute name="selected">
										<xsl:text>selected</xsl:text>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select="$locale/users/adm/settings/noActive"/>
							</option>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<label for="sendActiveNotify">
							<xsl:value-of select="$locale/users/adm/settings/activeNotify"/>
						</label>
					</th>
					<td>
						<input type="checkbox" id="sendActiveNotify" name="sendActiveNotify">
							<xsl:if test="@sendActiveNotify and @sendActiveNotify=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="sendNotify">
							<xsl:value-of select="$locale/users/adm/settings/notify"/>
						</label>
					</th>
					<td>
						<input type="checkbox" id="sendNotify" name="sendNotify" onchange="changeNotifyEnabler()">
							<xsl:if test="@sendNotify and @sendNotify=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/notifyList"/>
					</th>
					<td>
						<input type="text" name="notifyMails" class="full-width" id="notifyList">
							<xsl:if test="not(@sendNotify) or @sendNotify=''">
								<xsl:attribute name="disabled">
									<xsl:text>disabled</xsl:text>
								</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">
								<xsl:if test="@notifyMails and not(@notifyMails='')">
									<xsl:value-of select="@notifyMails"/>
								</xsl:if>
							</xsl:attribute>
						</input>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/users/adm/settings/passwordSettings"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 380px"/>
					<col/>
				</colgroup>

				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/minLength"/>
					</th>
					<td>
						<input type="number" name="length" min="0">
							<xsl:attribute name="value">
								<xsl:choose>
									<xsl:when test="@length and not(@length='')">
										<xsl:value-of select="@length"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>0</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/maxTry"/>
					</th>
					<td>
						<input type="number" name="attempts" min="0">
							<xsl:attribute name="value">
								<xsl:choose>
									<xsl:when test="@attempts and not(@attempts='')">
										<xsl:value-of select="@attempts"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>3</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="strongPasswordCheck">
							<xsl:value-of select="$locale/users/adm/settings/strongCheck"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="strong" id="strongPasswordCheck">
							<xsl:if test="@strong and @strong=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>

				<tr>
					<th>
						<label>
							<xsl:value-of select="$locale/users/adm/settings/passExpire"/>
						</label>
					</th>
					<td>
						<input type="number" name="passwordExpire">
							<xsl:attribute name="value">
								<xsl:choose>
									<xsl:when test="@passwordExpire and not(@passwordExpire='')">
										<xsl:value-of select="@passwordExpire"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>0</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
						</input>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/users/adm/recoverSettings"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 380px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/recoverTTL"/>
					</th>
					<td>
						<input type="number" name="recoverTTL">
							<xsl:attribute name="value">
								<xsl:choose>
									<xsl:when test="@recoverTTL and not(@recoverTTL='')">
										<xsl:value-of select="@recoverTTL"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:text>24</xsl:text>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
						</input>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/users/adm/settings/afterAuth"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 380px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/behavior"/>
					</th>
					<td>
						<label for="refresh">
							Refresh
							<input type="radio" value="refresh" name="behavior" id="refresh"
							       onchange="changeBehaviorEnabler( this );">
								<xsl:if test="@behavior='refresh'">
									<xsl:attribute name="checked">
										<xsl:text>checked</xsl:text>
									</xsl:attribute>
								</xsl:if>
							</input>
						</label>
						<label for="redirect">
							Redirect
							<input type="radio" value="redirect" name="behavior"
							       id="redirect" onchange="changeBehaviorEnabler( this );">
								<xsl:if test="@behavior='redirect'">
									<xsl:attribute name="checked">
										<xsl:text>checked</xsl:text>
									</xsl:attribute>
								</xsl:if>
							</input>
						</label>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/users/adm/settings/afterAddress"/>
					</th>
					<td>
						<input type="text" id="redirectURI" name="redirectURI" class="full-width">
							<xsl:if test="@behavior='refresh'">
								<xsl:attribute name="disabled">
									<xsl:text>disabled</xsl:text>
								</xsl:attribute>
							</xsl:if>
							<xsl:attribute name="value">
								<xsl:choose>
									<xsl:when test="@redirectURI=''">
										<xsl:text>/</xsl:text>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="@redirectURI"/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:attribute>
						</input>
					</td>
				</tr>
			</table>

			<div class="form-buttons">
				<input type="submit" value="{$locale/adm/save}"/>
			</div>
		</form>

	</xsl:template>
</xsl:stylesheet>

