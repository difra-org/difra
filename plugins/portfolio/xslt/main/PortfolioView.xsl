<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="PortfolioView">

		<h2>Portfolio</h2>

		<div class="portfolio">
			<xsl:for-each select="PortfolioEntry">
				<figure>
					<a href="/portfolio/{@uri}">

						<xsl:variable name="wId" select="@id"/>
						<xsl:variable name="picId" select="/root/content/PortfolioView/image[@portfolio=$wId]/@id"/>

						<div class="preview" style="background-image: url( '/portimages/{$picId}-medium.png' );"/>

						<figcaption>
							<xsl:value-of select="@name"/>
						</figcaption>
						<div class="button">View</div>
					</a>
				</figure>
			</xsl:for-each>
		</div>

	</xsl:template>
</xsl:stylesheet>