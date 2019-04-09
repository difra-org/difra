<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template name="bootstrap-headers">
        <xsl:choose>
            <xsl:when test="/root/@debug='1'">
                <link rel="stylesheet" href="/css/bootstrap/bootstrap.css"/>
                <script src="/js/bootstrap/bootstrap.js"/>
            </xsl:when>
            <xsl:otherwise>
                <link rel="stylesheet" href="/css/bootstrap/bootstrap.min.css"/>
                <script src="/js/bootstrap/bootstrap.min.js"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
