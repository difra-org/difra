<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="blogs-editPost">
		
		<h2>
			<a href="/adm/blogs">
				<xsl:value-of select="$locale/adm/blogs/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/adm/editPost/title"/>
		</h2>
		<h3>
			<xsl:value-of select="$locale/adm/addPost/options"/>
		</h3>
		<form name="addPost" id="addPost" method="post" action="/adm/blogs/updatepost/id/{/root/postData/@id}/">
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/addPost/userSelect"/>
					</th>
					<td>
						<select name="user">
							<xsl:for-each select="/root/users/item">
								<option value="{@id}">
									<xsl:if test="@id=/root/postData/@user">
										<xsl:attribute name="selected">
											<xsl:text>selected</xsl:text>
										</xsl:attribute>
									</xsl:if>
									<xsl:value-of select="@email"/>
								</option>
							</xsl:for-each>
						</select>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/addPost/postTitle"/>
					</th>
					<td>
						<input type="text" name="postTitle" id="postTitle" value="{/root/postData/@title}"/>
					</td>
				</tr>
			</table>
			<h3><xsl:value-of select="$locale/adm/addPost/text"/></h3>
			<textarea name="postText" id="postText" editor="Full" style="width: 100%;">
				<xsl:value-of select="/root/postData/@text" disable-output-escaping="yes" />
			</textarea>

			<input type="submit" class="large_spacing" id="sendWork" value="{$locale/adm/editPost/editButton}" />

		</form>

	</xsl:template>
</xsl:stylesheet>
