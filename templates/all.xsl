<!DOCTYPE xsl:stylesheet [
<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "xhtml-lat1.ent">
<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "xhtml-symbol.ent">
<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "xhtml-special.ent">
%lat1;
%symbol;
%special;
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:variable name="locale" select="/root/locale" />
	<xsl:param name="locale" select="$locale"/>
	<xsl:template match="/root/locale"/>
	<xsl:template match="/root/config"/>

	<xsl:include href="menu.xsl"/>
	<xsl:include href="form.xsl"/>

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
