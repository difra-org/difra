<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="announcementsSettings">

        <h2>
            <a href="/adm/announcements/">
                <xsl:value-of select="$locale/announcements/adm/announcements"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/announcements/adm/settings"/>
        </h2>

        <form method="post" action="/adm/announcements/savesettings/" class="ajaxer">
            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/maxPerUser"/>
                    </th>
                    <td>
                        <input name="maxPerUser" type="number" value="{@maxPerUser}" />
                        <div class="small gray">
                            <xsl:value-of select="$locale/announcements/adm/zeroValueHint"/>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/maxPerGroup"/>
                    </th>
                    <td>
                        <input name="maxPerGroup" type="number" value="{@maxPerGroup}"/>
                        <div class="small gray">
                            <xsl:value-of select="$locale/announcements/adm/zeroValueHint"/>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/imageSize"/>
                    </th>
                    <td>
                        <xsl:value-of select="$locale/announcements/adm/width"/>
                        <input name="width" type="number" value="{@width}"/>
                        <xsl:value-of select="$locale/announcements/adm/height"/>
                        <input name="height" type="number" value="{@height}"/>
                    </td>
                </tr>
            </table>

            <input type="submit" value="{$locale/adm/save}" />

        </form>

    </xsl:template>
</xsl:stylesheet>