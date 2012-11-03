<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="GalleryAlbumView">
		<h2>
			<a href="/adm/gallery">
				<xsl:value-of select="$locale/gallery/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/gallery/title-album"/> «<xsl:value-of select="@name"/>»
		</h2>
		<form action="/adm/gallery/album/add" class="ajaxer">
			<input type="hidden" name="album" value="{@id}"/>
			<input type="file" name="image[]" class="ajaxer" multiple="multiple" onchange="$(this).parent().submit()"/>
		</form>
		<br/>
		<xsl:choose>
			<xsl:when test="image">

                <xsl:variable name="width" select="sizes/m/@width"/>
                <xsl:variable name="height" select="sizes/m/@height"/>

				<xsl:for-each select="image">
					<div class="item-image">
						<div class="img" style="background-image: url('/gallery/{@id}m.{@format}'); width: {$width}px; height: {$height}px;" />
						<div class="controls">
							<xsl:call-template name="actionLeft">
								<xsl:with-param name="link">
									<xsl:text>/adm/gallery/album/up/</xsl:text>
									<xsl:value-of select="../@id"/>
									<xsl:text>/</xsl:text>
									<xsl:value-of select="@id"/>
								</xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="actionRight">
								<xsl:with-param name="link">
									<xsl:text>/adm/gallery/album/down/</xsl:text>
									<xsl:value-of select="../@id"/>
									<xsl:text>/</xsl:text>
									<xsl:value-of select="@id"/>
								</xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="actionDelete">
								<xsl:with-param name="link">
									<xsl:text>/adm/gallery/album/delete/</xsl:text>
									<xsl:value-of select="../@id"/>
									<xsl:text>/</xsl:text>
									<xsl:value-of select="@id"/>
								</xsl:with-param>
							</xsl:call-template>
						</div>
					</div>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$locale/gallery/adm/no-images"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>