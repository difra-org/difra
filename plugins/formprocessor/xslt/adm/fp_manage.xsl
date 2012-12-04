<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="FP_manage">

        <h2>
            <xsl:value-of select="$locale/formProcessor/adm/title"/>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/formProcessor/adm/manage/title"/>
        </h2>

        <a href="/adm/formprocessor/add" class="button">
            <xsl:value-of select="$locale/formProcessor/adm/create/addForm"/>
        </a>

        <h3><xsl:value-of select="$locale/formProcessor/adm/manage/forms"/></h3>

        <table>
            <tr>
                <th><xsl:value-of select="$locale/formProcessor/adm/manage/uri"/></th>
                <th><xsl:value-of select="$locale/formProcessor/adm/manage/name"/></th>
                <th><xsl:value-of select="$locale/formProcessor/adm/manage/fieldCount"/></th>
                <th><xsl:value-of select="$locale/formProcessor/adm/manage/status"/></th>
                <th><xsl:value-of select="$locale/formProcessor/adm/manage/actions"/></th>
            </tr>
            
            <xsl:for-each select="forms/form">
                <tr>
                    <td><xsl:value-of select="@uri"/></td>
                    <td><xsl:value-of select="@title"/></td>
                    <td><xsl:value-of select="@fieldsCount"/></td>
                    <td>
                        <xsl:choose>
                            <xsl:when test="@hidden=1">
                                <xsl:value-of select="$locale/formProcessor/adm/manage/formOff"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="$locale/formProcessor/adm/manage/formOn"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </td>
                    <td>
                        <a href="/adm/formprocessor/changestatus/{@id}" class="action down ajaxer" title="{$locale/formProcessor/adm/manage/disableForm}">
                            <xsl:value-of select="$locale/formProcessor/adm/manage/disableForm"/>
                        </a>
                        <a href="{@uri}" class="action view"/>
                        <a href="/adm/formprocessor/edit/{@id}" class="action edit"/>
                        <a href="/adm/formprocessor/delete/{@id}" class="action delete ajaxer"/>
                    </td>
                </tr>
            </xsl:for-each>
        </table>

    </xsl:template>
</xsl:stylesheet>
