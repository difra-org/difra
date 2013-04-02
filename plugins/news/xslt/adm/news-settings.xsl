<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template match="news-settings">

        <h2>
            <a href="/adm/news">
                <xsl:value-of select="$locale/news/adm/title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/news/adm/settings/title"/>
        </h2>

        <form class="ajaxer" method="post" action="/adm/news/savesettings/">
            <table class="form">
                <tr>
                    <th><xsl:value-of select="$locale/news/adm/settings/itemsPerPage"/></th>
                    <td>
                        <input type="number" name="perPage" value="{@perPage}" />
                    </td>
                </tr>
            </table>
            <input type="submit" value="{$locale/adm/save}"/>
        </form>

    </xsl:template>
</xsl:stylesheet>
