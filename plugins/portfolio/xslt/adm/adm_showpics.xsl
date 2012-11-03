<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-showpics">

        <h2>
            <a href="/adm/portfolio">
                <xsl:value-of select="$locale/view/title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/show/title"/> <xsl:text>«</xsl:text><xsl:value-of select="name"/><xsl:text>»</xsl:text>
        </h2>

		<h3><xsl:value-of select="$locale/show/main"/></h3>

		<div class="show-images">
			<label><xsl:value-of select="$locale/show/preview"/></label>
			<img src="/portimages/portfolio-{id}-small.jpg"/>
			<br/><br/>
			<label><xsl:value-of select="$locale/show/original"/></label>
			<a href="/portimages/portfolio-{id}-original.jpg" target="_blank"><img src="/portimages/portfolio-{id}-large.jpg"/></a>
		</div>

		<xsl:if test="additional-images/item">
			<h3><xsl:value-of select="$locale/show/additionals"/></h3>
			<div class="show-images">

				<xsl:for-each select="additional-images/item">
					<label><xsl:value-of select="$locale/show/preview"/></label>
					<a href="/portimages/portfolio-{@work_id}-{@image}-original.jpg" target="_blank">
						<img src="/portimages/portfolio-{@work_id}-{@image}-small.jpg"/>
					</a>
				</xsl:for-each>

			</div>
		</xsl:if>
		
	</xsl:template>
</xsl:stylesheet>
