<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="announcementsAdditionals">

        <h2>
            <a href="/adm/announcements/">
                <xsl:value-of select="$locale/announcements/adm/announcements"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/announcements/adm/additionals/title"/>
        </h2>

        <h3>
            <xsl:value-of select="$locale/announcements/adm/additionals/addNewField"/>
        </h3>

        <form class="ajaxer" method="post" action="/adm/announcements/additionals/save">

            <table class="addCategoryForm">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/additionals/fieldName"/>
                        <span class="small gray">
                            <xsl:value-of select="$locale/announcements/adm/additionals/nameExample"/>
                        </span>
                    </th>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/category/techAlias"/>
                        <span class="small gray">
                            <xsl:value-of select="$locale/announcements/adm/additionals/techAliasExample"/>
                        </span>
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="name" />
                    </td>
                    <td>
                        <div class="container">
                            <input type="text" name="alias" />
                            <div class="invalid">
                                <div class="invalid-text"/>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="submit" value="{$locale/adm/save}"/>
                    </td>
                </tr>
            </table>
        </form>

        <h3>
            <xsl:value-of select="$locale/announcements/adm/additionals/list"/>
        </h3>

        <xsl:if test="item">
            <table>
                <tr>
                    <th><xsl:value-of select="$locale/announcements/adm/category/num"/></th>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/additionals/fieldName"/>
                        <xsl:text> / </xsl:text>
                        <xsl:value-of select="$locale/announcements/adm/category/techAlias"/>
                    </th>
                    <th><xsl:value-of select="$locale/announcements/adm/actions"/></th>
                </tr>

                <xsl:for-each select="item">
                    <tr>
                        <td><xsl:value-of select="position()"/></td>
                        <td>
                            <div id="addField-{@id}">
                                <xsl:value-of select="@name"/>
                                <xsl:text> / </xsl:text>
                                <xsl:value-of select="@alias"/>
                            </div>

                            <div id="addField-{@id}-edit" class="addCategoryForm no-display">
                                <form class="ajaxer" action="/adm/announcements/additionals/save/" method="post">
                                    <div class="container categoryAdd">
                                        <input type="hidden" name="id" value="{@id}"/>
                                        <input type="hidden" name="originalAlias" value="{@alias}"/>
                                        <input type="text" name="name" value="{@name}"/>
                                        <xsl:text> / </xsl:text>
                                        <input type="text" name="alias" value="{@alias}"/>
                                        <input type="submit" value="{$locale/adm/save}"/>
                                        <div class="invalid">
                                            <div class="invalid-text"/>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </td>
                        <td>
                            <a href="#" class="action edit" onclick="announcementsUI.editAdditionals( {@id} );"/>
                            <a href="/adm/announcements/additionals/delete/{@id}/" class="action delete ajaxer"/>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>

        </xsl:if>

    </xsl:template>
</xsl:stylesheet>