<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="announcementsLocations">

        <h2>
            <a href="/adm/announcements/">
                <xsl:value-of select="$locale/announcements/adm/announcements"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/announcements/adm/locations/title"/>
        </h2>

        <form class="ajaxer" method="post" action="/adm/announcements/locations/save">
            <h3>
                <xsl:value-of select="$locale/announcements/adm/locations/addNewTitle"/>
            </h3>
            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/locations/name"/>
                    </th>
                    <td>
                        <input type="text" name="name"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/locations/www"/>
                    </th>
                    <td>
                        <input type="text" name="url" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/locations/address"/>
                    </th>
                    <td>
                        <input type="text" name="address" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/locations/phone"/>
                    </th>
                    <td>
                        <input type="text" name="phone" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/locations/info"/>
                    </th>
                    <td>
                        <input type="text" name="info" />
                    </td>
                </tr>
            </table>
            <input type="submit" value="{$locale/adm/actions/add}" />
        </form>

        <h3>
            <xsl:value-of select="$locale/announcements/adm/locations/title"/>
        </h3>

        <table>
            <tr>
                <th>
                    <xsl:value-of select="$locale/announcements/adm/locations/location"/>
                </th>
                <th>
                    <xsl:value-of select="$locale/announcements/adm/actions"/>
                </th>
            </tr>

            <xsl:for-each select="item">
                <tr>
                    <td>
                        <xsl:value-of select="@name"/>
                    </td>
                    <td>
                        <a href="/adm/announcements/locations/edit/{@id}/" class="action edit"/>
                        <a href="/adm/announcements/locations/delete/{@id}/" class="action delete ajaxer"/>
                    </td>
                </tr>
            </xsl:for-each>
        </table>

    </xsl:template>
</xsl:stylesheet>