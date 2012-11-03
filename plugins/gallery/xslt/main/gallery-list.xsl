<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="GalleryList">
		<h2>
			<xsl:value-of select="$locale/gallery/title"/>
		</h2>
		<xsl:apply-templates select="album" mode="list-item"/>
	</xsl:template>

	<xsl:template match="album" mode="list-item">

        <xsl:variable name="width" select="m/@width"/>
        <xsl:variable name="height" select="m/@height"/>

		<div class="gallery-album">
			<a href="/gallery/{@id}">
				<div class="gallery-album-image"
                     style="background-image:url('/gallery/{image[1]/@id}m.{@format}'); width: {$width}px; height: {$height}px;"/>
			</a>
			<div class="gallery-album-description">
                <h3>
				    <a href="/gallery/{@id}">
						<xsl:value-of select="@name"/>
				    </a>
                </h3>
				<div>
					<xsl:value-of select="@description" disable-output-escaping="yes"/>
				</div>
				<div class="clear"/>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>