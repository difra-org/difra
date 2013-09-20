<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="content/announcements-category-view">

		<div id="afisha-content">

			<xsl:for-each select="category/category">
				<xsl:variable name="catId" select="@id"/>

				<xsl:if test="/root/content/announcements-category-view/events/event[category=$catId]">

					<h2>
						<xsl:value-of select="@name"/>
					</h2>

					<span class="archiveLink">
						<xsl:choose>
							<xsl:when test="/root/auth/unauthorized">
								<a href="/authorization/" class="ajaxer">
									<xsl:value-of select="$locale/announcements/addEvent"/>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<!-- TODO: в случае если пользователь авторизован будет другая ссылка. -->


							</xsl:otherwise>
						</xsl:choose>
						<a href="/archive/{@category}/">
							<xsl:value-of select="$locale/announcements/archiveLink"/>
						</a>

						<div class="authForm"/>

					</span>
					<div class="clear"/>

					<div class="announcements">
						<xsl:for-each select="/root/content/announcements-category-view/events/event[category=$catId]">

							<span class="thumb">
								<xsl:if test="status='past'">
									<xsl:attribute name="class">
										<xsl:text>thumb archive</xsl:text>
									</xsl:attribute>
								</xsl:if>
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

				</xsl:if>
			</xsl:for-each>
		</div>

	</xsl:template>
</xsl:stylesheet>
