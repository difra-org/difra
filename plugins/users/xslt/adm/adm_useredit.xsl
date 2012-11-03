<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="userEdit">
		<h2>
			<a href="/adm/users">
				<xsl:value-of select="$locale/auth/adm/h2-title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/auth/adm/h3-useredit-common"/></h2>
		<h3>
			Основные параметры
		</h3>
		<xsl:choose>
			<xsl:when test="not(item)">
				<xsl:value-of select="$locale/auth/adm/user-not-found"/>
			</xsl:when>
			<xsl:otherwise>
				<form method="post" action="/adm/users/save/{item/@id}">
					<table class="form">
						<tr>
							<th>
								<xsl:value-of select="$locale/auth/adm/email"/>
							</th>
							<td>
								<input type="text" name="email" value="{item/@email}" id="email"/>
							</td>
						</tr>
						<tr>
							<th>
								<label for="changePw" class="checkbox_label">
									<xsl:value-of select="$locale/auth/adm/change-password"/>
								</label>
							</th>
							<td>
								<input type="checkbox"
								       name="change_pw"
								       id="changePw"
								       onchange="javascript:$('#newPw').attr('disabled',($('#changePw').is(':checked'))?'':'disabled')"/>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:value-of select="$locale/auth/adm/new-password"/>
							</th>
							<td>
								<input type="text" name="new_pw" id="newPw" disabled="disabled"/>
							</td>
						</tr>
					</table>
					<h3>
						<xsl:value-of select="$locale/auth/adm/additionals"/>
					</h3>
					<a href="#" onclick="addAdditionalField();" class="action" style="margin-bottom: 5px; ">
						<xsl:value-of select="$locale/auth/adm/addAditional"/>
					</a>
					<br/>
					<table class="form" id="addedFields">
						<xsl:if test="additionals/item">
							<xsl:for-each select="additionals/item">
								<tr>
									<th style="padding-left: 0">
										<input type="text"
										       name="additional_name[]"
										       value="{@name}"/>
									</th>
									<td>
										<input type="text"
										       name="additional_value[]"
										       value="{@value}"/>
									</td>
								</tr>
							</xsl:for-each>
						</xsl:if>
					</table>
					<input type="submit" class="large_spacing" value="{$locale/auth/adm/save}"/>
				</form>
				<xsl:if test="item/info">
					<h3><xsl:value-of select="$locale/auth/adm/h3-useredit-info"/></h3>
					<table>
						<xsl:for-each select="item/info/@*">
							<xsl:variable name="name" select="name()"/>
							<tr>
								<th>
									<xsl:value-of select="$locale/auth/info/*[name()=$name]"/><xsl:text>:</xsl:text>
								</th>
								<td><xsl:value-of select="."/></td>
							</tr>
						</xsl:for-each>
					</table>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>

