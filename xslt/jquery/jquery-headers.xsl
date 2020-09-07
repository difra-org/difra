<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template name="jquery-headers">
        <xsl:choose>
            <xsl:when test="/root/@debug='1'">
                <script src="/js/jquery/jquery-3.5.1.js"/>
            </xsl:when>
            <xsl:otherwise>
                <script src="/js/jquery/jquery-3.5.1.min.js"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
