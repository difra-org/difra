<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/content/catalogCategories">
		<xsl:call-template name="catalogSubcategory"/>
	</xsl:template>
	<xsl:template name="catalogSubcategory">
		<xsl:param name="node" select="."/>
		<xsl:param name="parent" select="0"/>
		<xsl:if test="$node/category[@parent=$parent]">
			<ul>
				<xsl:for-each select="$node/category[@parent=$parent]">
					<xsl:if test="not(@emptyHidden='1')">
						<li>
							<xsl:if test="@selected=1">
								<xsl:attribute name="class">selected</xsl:attribute>
							</xsl:if>
							<a href="{@link}">
								<xsl:value-of select="@name"/>
							</a>
							<xsl:call-template name="catalogSubcategory">
								<xsl:with-param name="parent" select="@id"/>
								<xsl:with-param name="node" select=".."/>
							</xsl:call-template>
						</li>
					</xsl:if>
				</xsl:for-each>
			</ul>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>