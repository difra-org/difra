<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="GalleryAlbumAdd">
		<h2>
			<a href="/adm/gallery/albums">
				<xsl:value-of select="$locale/gallery/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/gallery/title-add"/>
		</h2>
		<xsl:call-template name="GalleryAlbumForm"/>
	</xsl:template>
	<xsl:template match="GalleryAlbumEdit">
		<h2>
			<a href="/adm/gallery/albums/">
				<xsl:value-of select="$locale/gallery/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/gallery/title-edit"/>
		</h2>
		<xsl:call-template name="GalleryAlbumForm"/>
	</xsl:template>
	<xsl:template name="GalleryAlbumForm">
		<form action="/adm/gallery/albums/save" class="ajaxer form">
			<xsl:if test="name()='GalleryAlbumEdit'">
				<input type="hidden" name="id" value="{@id}"/>
			</xsl:if>
			<h3><xsl:value-of select="$locale/gallery/adm/edit/title"/></h3>
			<table class="form">
				<colgroup>
					<col style="width: 230px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/gallery/adm/edit/name"/>
					</th>
					<td>
						<input type="text" name="name" value="{@name}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/gallery/adm/edit/hidden"/>
					</th>
					<td>
						<input type="checkbox" name="hidden">
							<xsl:if test="@visible=0">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>
			<h3><xsl:value-of select="$locale/gallery/adm/edit/description"/></h3>
			<textarea name="description" editor="Full">
				<xsl:value-of select="@description"/>
			</textarea>
			<br />
			<input type="submit" value="{$locale/adm/save}"/>
		</form>
	</xsl:template>
</xsl:stylesheet>