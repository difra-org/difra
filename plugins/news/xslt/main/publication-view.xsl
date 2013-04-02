<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="publication-view">

        <div class="publication">
            <h2>
                <xsl:value-of select="publication/@title"/>
            </h2>
            <span class="pubDate">
                <xsl:value-of select="publication/@pubDate"/>
            </span>
            <span class="pubText">
                <xsl:value-of select="publication/@body" disable-output-escaping="yes"/>
            </span>
        </div>

    </xsl:template>
</xsl:stylesheet>
