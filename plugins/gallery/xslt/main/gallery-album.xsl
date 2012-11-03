<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="GalleryAlbum">
		<h2>
			<a href="/gallery">
				<xsl:value-of select="$locale/gallery/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="@name"/>
		</h2>
		<div id="album-images">
			<xsl:apply-templates select="image" mode="thumbnail"/>
		</div>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
		<div class="thumb-fill"/>
	</xsl:template>

	<xsl:template match="image" mode="thumbnail">
		<a href="/gallery/{@id}l.png" onclick="gallery.view(event,{@id}, '{@format}')" id="galleryThumb_{@id}" class="gallery noAjaxer">
			<span class="thumb" style="background-image:url('/gallery/{@id}s.{@format}')"/>
		</a>
		<xsl:text> </xsl:text>
	</xsl:template>
</xsl:stylesheet>