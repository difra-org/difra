<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="video-manager">

		<h2><xsl:value-of select="$locale/videoManager/adm/manageTitle"/></h2>


		<h3><xsl:value-of select="$locale/videoManager/adm/inDir"/></h3>
		<xsl:call-template name="video-in"/>


		<h3><xsl:value-of select="$locale/videoManager/adm/outDir"/></h3>
		<xsl:call-template name="video-out"/>

	</xsl:template>
</xsl:stylesheet>