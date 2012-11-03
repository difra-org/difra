<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/CMSMenu">
		<ul id="menu_{@name}">
			<xsl:for-each select="menuitem">
				<li>
					<xsl:choose>
						<xsl:when test="page">
							<a href="{page/@uri}">
								<xsl:value-of select="page/@title"/>
							</a>
						</xsl:when>
						<xsl:when test="@link">
							<a href="{@link}">
								<xsl:value-of select="@linkLabel"/>
							</a>
						</xsl:when>
					</xsl:choose>
				</li>
			</xsl:for-each>
		</ul>
	</xsl:template>
</xsl:stylesheet>
