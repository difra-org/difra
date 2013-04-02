<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="announcements-archive-view">

		<div id="afisha-content">
			<h2>
				<xsl:value-of select="$locale/announcements/archive"/>
				<xsl:text> «</xsl:text>
				<xsl:value-of select="@category"/>
				<xsl:text>»</xsl:text>
			</h2>
			<div class="clear"/>

			<div class="announcements">
				<xsl:for-each select="events/event">
					<span class="thumb">
						<a href="/events/{link}">
							<img src="/announcements/{id}.png" alt=""/>
							<span class="announcement-date">

								<xsl:call-template name="announcements-dates">
									<xsl:with-param name="format" select="string('cut')"/>
								</xsl:call-template>

							</span>
							<span class="announcement-title">
								<xsl:value-of select="title"/>
							</span>
							<span class="announcement-place">
								<xsl:value-of select="additionals/field[@alias='eventPlace']/@value"/>
							</span>
						</a>
					</span>
				</xsl:for-each>
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

			<div class="paginator">
				<xsl:call-template name="paginator">
					<xsl:with-param name="link">
						<xsl:value-of select="/root/announcements-archive-view/events/@link"/>
					</xsl:with-param>
					<xsl:with-param name="pages">
						<xsl:value-of select="/root/announcements-archive-view/events/@pages"/>
					</xsl:with-param>
					<xsl:with-param name="current">
						<xsl:value-of select="/root/announcements-archive-view/events/@current"/>
					</xsl:with-param>
				</xsl:call-template>
			</div>
			<div class="clear"/>
		</div>

	</xsl:template>
</xsl:stylesheet>