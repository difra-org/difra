<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root">
		<xsl:choose>
			<xsl:when test="/root/@ajax=1">
				<html>
					<body>
						<xsl:call-template name="content-wrapper"/>
					</body>
				</html>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="html"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="html">
		<html>
			<xsl:call-template name="html-head"/>
			<xsl:call-template name="html-body"/>
		</html>
	</xsl:template>

	<xsl:template name="html-body">
		<body>
			<xsl:call-template name="content-wrapper"/>
		</body>
	</xsl:template>

	<xsl:template name="content-wrapper">
		<div id="content">
			<xsl:call-template name="content"/>
		</div>
	</xsl:template>

	<xsl:template name="content">
		<xsl:apply-templates select="*"/>
	</xsl:template>
</xsl:stylesheet>