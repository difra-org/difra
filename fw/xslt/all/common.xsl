<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:variable name="localePath" select="/root/locale" />
	<xsl:param name="locale" select="$localePath"/>

	<xsl:template match="/root/locale"/>

	<xsl:template name="config">
		<!--
		<script type="text/javascript">
			var config = {};
			<xsl:for-each select="@*">
				<xsl:text>config.</xsl:text>
				<xsl:value-of select="name()"/>
				<xsl:text> = "</xsl:text>
				<xsl:value-of select="."/>
				<xsl:text>";</xsl:text>
			</xsl:for-each>
		</script>
		-->
		<xsl:comment>WARNING: 'config' template is deprecated</xsl:comment>
	</xsl:template>

	<xsl:template name="wrappers">
		<xsl:comment>WARNING: 'wrappers' template is deprecated</xsl:comment>
	</xsl:template>

	<xsl:template name="repeat">
		<xsl:param name="times"/>
		<xsl:param name="text"/>
		<xsl:if test="$times &gt; 0">
			<xsl:value-of select="$text"/>
			<xsl:call-template name="repeat">
				<xsl:with-param name="times"><xsl:value-of select="$times - 1"/></xsl:with-param>
				<xsl:with-param name="text"><xsl:value-of select="$text"/></xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>
