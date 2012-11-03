<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="blogs-overView">
	
		<xsl:variable name="link" select="string('/adm/blogs')"/>

		<h2><xsl:value-of select="$locale/adm/blogs/title"/></h2>
		<a href="/adm/blogs/addpost" class="button">
			<xsl:value-of select="$locale/adm/blogs/addpost"/>
		</a>
		<h3>
			<xsl:value-of select="$locale/adm/blogs/h3"/>
		</h3>
		<table>
			<tr>
				<th>
					<xsl:value-of select="$locale/adm/blogs/post-title"/>
				</th>
				<th>
					<xsl:value-of select="$locale/adm/blogs/post-content"/>
				</th>
				<th>
					<xsl:value-of select="$locale/adm/blogs/actions"/>
				</th>
			</tr>
			<xsl:for-each select="/root/blogs/post">
				<tr>
					<td class="post-title">
						<div>
							<xsl:value-of select="@title"/>
						</div>
					</td>
					<td class="post-text">
						<div>
							<xsl:value-of select="@preview" disable-output-escaping="yes"/>
						</div>
					</td>
					<td>
						<a href="/adm/blogs/editpost/id/{@id}" class="action edit"></a>
						<a href="/adm/blogs/deletepost/id/{@id}" class="action delete"></a>
					</td>
				</tr>
			</xsl:for-each>
		</table>
		<br/>
		<xsl:call-template name="paginator">
			<xsl:with-param name="pages" select="/root/blogs/@pages"/>
			<xsl:with-param name="current" select="/root/blogs/@current"/>
			<xsl:with-param name="link" select="$link"/>
		</xsl:call-template>		

	</xsl:template>
</xsl:stylesheet>
