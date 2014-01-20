<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="PortfolioWork">

		<div class="index-block">
			<div class="column">

				<div style="width: 50%" class="portfolioColumns">
					<!-- картинки портфолио -->
					<xsl:for-each select="PortfolioImagesList/PortfolioImages">
						<a href="#" onclick="portfolio.view(event,{@id}, 'png', {position()})" id="portfolioThumb_{position()}">
							<img src="/portimages/{@id}-full.png" />
						</a>

					</xsl:for-each>
				</div>

				<div style="width: 50%" class="portfolioDescription">
					<!-- описание портфолио -->

					<h2><xsl:value-of select="@name"/></h2>

					<span class="date">
						<xsl:value-of select="@fullDate"/>
					</span>

					<span class="description">
						<xsl:value-of select="@description" disable-output-escaping="yes"/>
					</span>

					<span class="link">
						<xsl:choose>
							<xsl:when test="@link_caption and not(@link_caption='')">
								<a href="{@link}">
									<xsl:value-of select="@link_caption"/>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<a href="{@link}">
									<xsl:value-of select="@link"/>
								</a>
							</xsl:otherwise>
						</xsl:choose>
					</span>

					<div class="roles">
						<xsl:for-each select="role">
							<label>
								<xsl:value-of select="@name"/>
							</label>
							<xsl:for-each select="contibutor">
								<span>
									<xsl:value-of select="@name"/>
								</span>
							</xsl:for-each>
						</xsl:for-each>

						<xsl:if test="@software and not(@software='')">
							<label>
								<xsl:value-of select="$locale/portfolio/entry/software"/>
							</label>
							<span>
								<xsl:value-of select="@software"/>
							</span>
						</xsl:if>
					</div>
				</div>


			</div>
		</div>

	</xsl:template>
</xsl:stylesheet>