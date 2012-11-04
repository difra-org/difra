<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="GalleryAlbumList">
		<h2><xsl:value-of select="$locale/gallery/title"/></h2>
		<a href="/adm/gallery/add" class="button">
			<xsl:value-of select="$locale/gallery/adm/list/add-album"/>
		</a>
		<h3>
			<xsl:value-of select="$locale/gallery/adm/list/albums-list"/>
		</h3>
		<xsl:choose>
			<xsl:when test="not(album)">
				<span class="message">
					<xsl:value-of select="$locale/gallery/adm/list/no-albums"/>
				</span>
			</xsl:when>
			<xsl:otherwise>
				<table>
					<tr>
						<th>
							<xsl:value-of select="$locale/gallery/adm/list/name"/>
						</th>
						<th>
							<xsl:value-of select="$locale/adm/actions/title"/>
						</th>
					</tr>
					<xsl:for-each select="album">
						<tr>
							<td>
									<xsl:value-of select="@name"/>
							</td>
							<td>
								<xsl:call-template name="actionEdit">
									<xsl:with-param name="link">
										<xsl:text>/adm/gallery/edit/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="actionContent">
									<xsl:with-param name="link">
										<xsl:text>/adm/gallery/album/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="actionUp">
									<xsl:with-param name="link">
										<xsl:text>/adm/gallery/up/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="actionDown">
									<xsl:with-param name="link">
										<xsl:text>/adm/gallery/down/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="actionDelete">
									<xsl:with-param name="link">
										<xsl:text>/adm/gallery/delete/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>