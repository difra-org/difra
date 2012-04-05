<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="menu">
		<xsl:param name="auto" select="1"/>
		<xsl:if test="$auto=0">
			<div id="menu">
				<xsl:call-template name="common_menu"/>
			</div>
		</xsl:if>
	</xsl:template>

	<xsl:template name="common_menu">
		<xsl:if test="*">
			<xsl:variable name="instance" select="/root/menu/@instance"/>
			<ul>
				<xsl:for-each select="*">
					<xsl:sort select="@priority" order="descending"/>
					<xsl:if test="not(@hidden=1)">
						<li id="{@id}">
							<xsl:if test="@sup=1">
								<xsl:attribute name="class">sup</xsl:attribute>
							</xsl:if>
							<!-- получаем название пункта меню -->
							<xsl:variable name="title">
								<xsl:choose>
									<xsl:when test="@title">
										<xsl:value-of select="@title"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:variable name="id" select="@id"/>
										<xsl:choose>
											<xsl:when test="$locale/menu/*[name()=$instance]/*[name()=$id]">
												<xsl:value-of select="$locale/menu/*[name()=$instance]/*[name()=$id]"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="name()"/>
											</xsl:otherwise>
										</xsl:choose>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:variable>
							<xsl:choose>
								<xsl:when test="@href=''">
									<span class="menu_nolink">
										<xsl:value-of select="$title"/>
									</span>
								</xsl:when>
								<xsl:when test="@href and not(@href=/root/@menuitem)">
									<a href="{@href}">
										<xsl:value-of select="$title"/>
									</a>
								</xsl:when>
								<xsl:when test="@href=''">
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
					</xsl:if>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
