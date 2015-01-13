<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="publication-add">

        <h2>
            <a href="/adm/news">
                <xsl:value-of select="$locale/news/adm/title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/news/adm/add/title"/>
        </h2>

        <form class="ajaxer" method="post" action="/adm/news/save">

            <h3>
                <xsl:value-of select="$locale/news/adm/add/mainBlock"/>
            </h3>
            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/newsTitle"/>
                    </th>
                    <td>
                        <input type="text" name="title" />
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/pubDate"/>
                    </th>
                    <td>
                        <input type="text" name="pubDate" value="{/root/date/@x}" id="pubDate"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/viewDate"/>
                    </th>
                    <td>
                        <input type="text" name="viewDate" value="{/root/date/@x}" id="viewDate"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/stopDate"/>
                    </th>
                    <td>
                        <input type="text" name="stopDate" id="stopDate"/>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/visible"/>
                    </th>
                    <td>
                        <input type="checkbox" name="visible" checked="checked"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/important"/>
                    </th>
                    <td>
                        <input type="checkbox" name="important" />
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
                        <textarea rows="" cols="" name="announcement" editor="Full" bodyclass="page" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/body"/>
                    </th>
                    <td>
                        <textarea rows="" cols="" name="body" editor="Full" bodyclass="page"/>
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
                        <input type="text" name="sourceName" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/news/adm/add/sourceURL"/>
                    </th>
                    <td>
                        <input type="text" name="sourceURL"/>
                    </td>
                </tr>
            </table>

            <input type="submit" value="{$locale/adm/actions/add}"/>

        </form>

    </xsl:template>
</xsl:stylesheet>
