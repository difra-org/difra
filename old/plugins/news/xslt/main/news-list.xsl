<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="news-list">

        <xsl:for-each select="publication">

            <div class="publication">
                <h3>
                    <a href="{@trueLink}">
                        <xsl:value-of select="@title"/>
                    </a>
                </h3>
                <span class="pubDate">
                    <xsl:value-of select="@pubDate"/>
                </span>
                <span class="announcement">
                    <xsl:choose>
                        <xsl:when test="not(@announcement='')">
                            <xsl:value-of select="@announcement" disable-output-escaping="yes"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="@body" disable-output-escaping="yes"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </span>
                <span class="readMore">
                    <a href="{@trueLink}">
                        <xsl:value-of select="$locale/news/readMore"/>
                    </a>
                </span>
                <span class="source">
                    <xsl:choose>
                        <xsl:when test="@sourceURL and @sourceName">
                            <a href="{@sourceURL}">
                                <xsl:value-of select="@sourceName"/>
                            </a>
                        </xsl:when>
                        <xsl:when test="@sourceURL">
                            <a href="{@sourceURL}">
                                <xsl:value-of select="@sourceURL"/>
                            </a>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="@sourceName"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </span>
            </div>

        </xsl:for-each>

        <!-- постраничник -->
        <xsl:if test="/root/news-list/@pages&gt;1">
            <div class="paginator">
                <xsl:call-template name="paginator">
                    <xsl:with-param name="link">
                        <xsl:value-of select="/root/news-list/@link"/>
                    </xsl:with-param>
                    <xsl:with-param name="pages">
                        <xsl:value-of select="/root/news-list/@pages"/>
                    </xsl:with-param>
                    <xsl:with-param name="current">
                        <xsl:value-of select="/root/news-list/@current"/>
                    </xsl:with-param>
                </xsl:call-template>
            </div>
        </xsl:if>

    </xsl:template>
</xsl:stylesheet>
