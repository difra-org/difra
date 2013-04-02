<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="announcementsCategory">

        <h2>
            <a href="/adm/announcements/">
                <xsl:value-of select="$locale/announcements/adm/announcements"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/announcements/adm/category/title"/>
        </h2>

        <h3><xsl:value-of select="$locale/announcements/adm/category/addCategory"/></h3>

        <form class="ajaxer addCategoryForm" action="/adm/announcements/category/save/" method="post">
            <table>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/category/name"/>
                        <span class="small gray">
                            <xsl:value-of select="$locale/announcements/adm/category/catNameExample"/>
                        </span>
                    </th>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/category/techAlias"/>
                        <span class="small gray">
                            <xsl:value-of select="$locale/announcements/adm/category/techAliasExample"/>
                        </span>
                    </th>
                    <th></th>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="categoryName" />
                    </td>
                    <td>
                        <div class="container categoryAdd">
                            <input type="text" name="categoryAlias"/>
                            <div class="invalid">
                                <div class="invalid-text"/>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="submit" value="{$locale/adm/actions/add}"/>
                    </td>
                </tr>
            </table>
        </form>

        <h3><xsl:value-of select="$locale/announcements/adm/category/list"/></h3>
        
        <xsl:if test="category">
            <table id="announcements-categoryList">
                <tr>
                    <th><xsl:value-of select="$locale/announcements/adm/category/num"/></th>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/category/name"/>
                        <xsl:text> / </xsl:text>
                        <xsl:value-of select="$locale/announcements/adm/category/techAlias"/>
                    </th>
                    <th><xsl:value-of select="$locale/announcements/adm/actions"/></th>
                </tr>

                <xsl:for-each select="category">
                    <tr>
                        <td><xsl:value-of select="position()"/></td>
                        <td>
                            <div id="ann-category-{@id}">
                                <xsl:value-of select="@name"/>
                                <xsl:text> / </xsl:text>
                                <xsl:value-of select="@category"/>
                            </div>
                            <div id="ann-category-{@id}-edit" class="no-display">
                                <form class="ajaxer" action="/adm/announcements/category/save/" method="post">
                                    <div class="container categoryAdd">
                                        <input type="hidden" name="catId" value="{@id}" />
                                        <input type="hidden" name="originalAlias" value="{@category}"/>
                                        <input type="text" name="categoryName" value="{@name}" />
                                        <xsl:text> / </xsl:text>
                                        <input type="text" name="categoryAlias" value="{@category}"/>
                                        <input type="submit" value="{$locale/adm/save}"/>
                                        <div class="invalid">
                                            <div class="invalid-text"/>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </td>
                        <td>
                            <a href="#" class="action edit" onclick="announcementsUI.editCategory( {@id} );"/>
                            <a href="/adm/announcements/category/delete/{@id}/" class="action delete ajaxer"/>
                        </td>
                    </tr>
                </xsl:for-each>
            </table>
        </xsl:if>

    </xsl:template>
</xsl:stylesheet>