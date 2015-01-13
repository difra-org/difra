<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="news-view">

        <h2>
            <xsl:value-of select="$locale/news/adm/title"/>
        </h2>
        <a href="/adm/news/add/" class="button">
            <xsl:value-of select="$locale/news/adm/addNews"/>
        </a>

        <h3><xsl:value-of select="$locale/news/adm/currentList"/></h3>

        <xsl:if test="publication">
            <table class="newsList">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/date"/>
                    </th>
                    <th>
                        <xsl:value-of select="$locale/news/adm/pubTitle"/>
                    </th>
                    <th>
                        <xsl:value-of select="$locale/news/adm/status"/>
                    </th>
                    <th>

                    </th>
                    <th>
                        <xsl:value-of select="$locale/news/adm/actions"/>
                    </th>
                </tr>

                <xsl:for-each select="publication">
                    <tr>
                        <xsl:if test="@visible=0">
                            <xsl:attribute name="class">
                                <xsl:text>noVisible</xsl:text>
                            </xsl:attribute>
                        </xsl:if>
                        <td>
                            <xsl:value-of select="@pubDate"/>
                            <span class="small">
                                <xsl:value-of select="$locale/news/adm/viewFrom"/>
                                <xsl:text>&#160;</xsl:text>
                                <xsl:value-of select="@viewDate"/>

                                <xsl:if test="@stopDate">
                                    <xsl:text>&#160;</xsl:text>
                                    <xsl:value-of select="$locale/news/adm/viewTo"/>
                                    <xsl:text>&#160;</xsl:text>
                                    <xsl:value-of select="@stopDate"/>
                                </xsl:if>
                            </span>
                        </td>
                        <td>
                            <xsl:value-of select="@title"/>
                            <xsl:if test="@sourceName or @sourceURL">
                                <span class="small">
                                    <xsl:value-of select="$locale/news/adm/source"/>
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
                            </xsl:if>
                        </td>
                        <td>
                            <xsl:if test="@visible=1">
                                <xsl:value-of select="$locale/news/adm/visible"/>
                            </xsl:if>

                            <xsl:if test="@visible=1 and @important=1">
                                <xsl:text>,&#160;&#160;</xsl:text>
                            </xsl:if>
                            <xsl:if test="@important=1">
                                <xsl:value-of select="$locale/news/adm/important"/>
                            </xsl:if>
                        </td>
                        <td>
                            <xsl:choose>
                                <xsl:when test="@visible=1">
                                    <a href="/adm/news/status/{@id}/off/" class="action ajaxer changeVisible">
                                        <xsl:value-of select="$locale/news/adm/off"/>
                                    </a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <a href="/adm/news/status/{@id}/on/" class="action ajaxer changeVisible">
                                        <xsl:value-of select="$locale/news/adm/on"/>
                                    </a>
                                </xsl:otherwise>
                            </xsl:choose>
                            <xsl:choose>
                                <xsl:when test="@important=1">
                                    <a href="/adm/news/important/{@id}/off/" class="action ajaxer changeImportant">
                                        <xsl:value-of select="$locale/news/adm/makeNormal"/>
                                    </a>
                                </xsl:when>
                                <xsl:otherwise>
                                    <a href="/adm/news/important/{@id}/on/" class="action ajaxer changeImportant">
                                        <xsl:value-of select="$locale/news/adm/makeImportant"/>
                                    </a>
                                </xsl:otherwise>
                            </xsl:choose>
                        </td>
                        <td>
                            <a href="{@trueLink}" class="action view"></a>
                            <a href="/adm/news/edit/{@id}/" class="action edit"/>
                            <a href="/adm/news/delete/{@id}/" class="action delete ajaxer"/>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </xsl:if>

    </xsl:template>
</xsl:stylesheet>
