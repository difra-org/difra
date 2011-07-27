<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [
<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "common/xhtml-lat1.ent">
<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "common/xhtml-symbol.ent">
<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "common/xhtml-special.ent">
%lat1;
%symbol;
%special;
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/index">
		<div id="intro_page">
			<xsl:value-of select="$locale/index/welcome"/>
		</div>
	</xsl:template>
</xsl:stylesheet>
