<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="last-comments">

		<h2>
			<xsl:value-of select="$locale/comments/adm/lastComments"/>
		</h2>

		<xsl:for-each select="comments/*">
			<xsl:variable name="moduleName" select="@module"/>
			<h3><xsl:value-of select="$locale/comments/adm/moduleComments"/>«<xsl:value-of select="@module"/>»</h3>
			<table class="last-comments">
				<tr>
					<th><xsl:value-of select="$locale/comments/adm/user"/></th>
					<th>Заголовок</th>
					<th><xsl:value-of select="$locale/comments/adm/date"/></th>
					<th><xsl:value-of select="$locale/comments/adm/text"/></th>
					<th></th>
				</tr>
				<xsl:for-each select="current()/item">
					<tr>
						<td><xsl:value-of select="@nickname"/></td>
						<td>

								<xsl:if test="not(@reply_id='')">
									<span class="gray">
										<xsl:text>Re: </xsl:text>
									</span>
								</xsl:if>
							<a href="{@parentLink}">
								<xsl:value-of select="@title"/>
							</a>

						</td>
						<td class="date"><xsl:value-of select="@date"/></td>
						<td>
							<div>
								<xsl:value-of select="@text"/>
							</div>
						</td>
						<td>
							<a href="/adm/comments/delete/{$moduleName}/{@id}/" class="ajaxer action delete">Удалить</a>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:for-each>

	</xsl:template>
</xsl:stylesheet>

