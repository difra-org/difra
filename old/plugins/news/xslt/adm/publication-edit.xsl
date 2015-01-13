<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="publication-edit">

        <h2>
            <a href="/adm/news">
                <xsl:value-of select="$locale/news/adm/title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/news/adm/edit"/>
        </h2>

        <form class="ajaxer" method="post" action="/adm/news/save">

            <input type="hidden" name="id" value="{publication/@id}" />

            <h3>
                <xsl:value-of select="$locale/news/adm/add/mainBlock"/>
            </h3>
            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/newsTitle"/>
                    </th>
                    <td>
                        <input type="text" name="title" value="{publication/@title}"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/pubDate"/>
                    </th>
                    <td>
                        <input type="text" name="pubDate" value="{publication/@pubDate}" id="pubDate"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/viewDate"/>
                    </th>
                    <td>
                        <input type="text" name="viewDate" value="{publication/@viewDate}" id="viewDate"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/stopDate"/>
                    </th>
                    <td>
                        <input type="text" name="stopDate" id="stopDate" value="{publication/@stopDate}"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/visible"/>
                    </th>
                    <td>
                        <input type="checkbox" name="visible">
                            <xsl:if test="publication/@visible=1">
                                <xsl:attribute name="checked">
                                    <xsl:text>checked</xsl:text>
                                </xsl:attribute>
                            </xsl:if>
                        </input>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/important"/>
                    </th>
                    <td>
                        <input type="checkbox" name="important">
                            <xsl:if test="publication/@important=1">
                                <xsl:attribute name="checked">
                                    <xsl:text>checked</xsl:text>
                                </xsl:attribute>
                            </xsl:if>
                        </input>
                    </td>
                </tr>
            </table>

            <h3>
                <xsl:value-of select="$locale/news/adm/add/pubTexts"/>
            </h3>
            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/announcement"/>
                    </th>
                    <td>
                        <textarea rows="" cols="" name="announcement" editor="Full" bodyclass="page">
                            <xsl:value-of select="publication/@announcement" disable-output-escaping="yes"/>
                        </textarea>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/body"/>
                    </th>
                    <td>
                        <textarea rows="" cols="" name="body" editor="Full" bodyclass="page">
                            <xsl:value-of select="publication/@body" disable-output-escaping="yes"/>
                        </textarea>
                    </td>
                </tr>
            </table>

            <h3>
                <xsl:value-of select="$locale/news/adm/add/source"/>
            </h3>
            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/sourceName"/>
                    </th>
                    <td>
                        <input type="text" name="sourceName" value="{publication/@sourceName}"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/sourceURL"/>
                    </th>
                    <td>
                        <input type="text" name="sourceURL" value="{publication/@sourceURL}"/>
                    </td>
                </tr>
            </table>

            <input type="submit" value="{$locale/adm/save}"/>

        </form>

    </xsl:template>
</xsl:stylesheet>
