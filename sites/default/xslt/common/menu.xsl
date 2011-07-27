<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="menu">
		<div id="menu">
			<xsl:call-template name="common_menu"/>
		</div>
	</xsl:template>

	<xsl:template name="common_menu">
		<xsl:if test="item">
			<ul>
				<xsl:for-each select="item">
					<li id="menu_{@id}">
						<xsl:variable name="title">
							<xsl:choose>
								<xsl:when test="@title">
									<xsl:value-of select="@title"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:variable name="id" select="@id"/>
									<xsl:value-of select="$locale/menu/*[name()=$id]"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>
						<xsl:choose>
							<xsl:when test="@href and not(@href=/root/@menuitem)">
								<a href="{@href}">
									<xsl:value-of select="$title"/>
								</a>
							</xsl:when>
							<xsl:when test="not(@href)">
								<span class="menu_nolink">
									<xsl:value-of select="$title"/>
								</span>
							</xsl:when>
							<xsl:otherwise>
								<span class="menu_active">
									<xsl:value-of select="$title"/>
								</span>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:call-template name="common_menu"/>
					</li>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
