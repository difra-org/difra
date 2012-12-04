<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="announcementsAdd">

        <h2>
            <a href="/adm/announcements/">
                <xsl:value-of select="$locale/announcements/adm/announcements"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/announcements/adm/add"/>
        </h2>

        <form action="/adm/announcements/save/" class="ajaxer" method="post">

            <h3><xsl:value-of select="$locale/announcements/adm/forms/pic"/></h3>

            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/imagePreview"/>
                    </th>
                    <td><input type="file" name="eventImage" accept="image/jpeg,image/png,image/gif" /></td>
                </tr>
            </table>

            <h3>
                <xsl:value-of select="$locale/announcements/adm/forms/mainParameters"/>
            </h3>

            <table class="form">

                <xsl:if test="newGroups/group">

                    <tr>
                        <th>
                            <xsl:value-of select="$locale/announcements/adm/forms/group"/></th>
                        <td>
                            <select name="group">
                                <xsl:for-each select="newGroups/group">
                                    <option value="{@id}">
                                        <xsl:if test="@id=1">
                                            <xsl:attribute name="selected">
                                                <xsl:text>selected</xsl:text>
                                            </xsl:attribute>
                                        </xsl:if>
                                        <xsl:value-of select="@name"/>
                                    </option>
                                </xsl:for-each>
                            </select>
                        </td>
                    </tr>
                </xsl:if>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/title"/>
                    </th>
                    <td>
                        <input type="text" name="title" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/eventDate"/>
                    </th>
                    <td>
                        <input type="text" name="eventDate" id="eventDate" />
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/beginDate"/>
                    </th>
                    <td>
                        <input type="text" name="beginDate" id="beginDate" disabled="disabled"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/endDate"/>
                    </th>
                    <td>
                        <input type="text" name="endDate" id="endDate" disabled="disabled"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/priority"/>
                    </th>
                    <td>
                        <input type="hidden" id="priorityValue" name="priorityValue" value="50" />
                        <div id="prioritySlider"/>
                        <div id="priorityValueView">50</div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/visibility"/>
                    </th>
                    <td>
                        <input type="checkbox" name="visible" value="1" checked="checked"/>
                    </td>
                </tr>
            </table>

            <xsl:if test="additionalsFields/item">
                <h3>
                    <xsl:value-of select="$locale/announcements/adm/additionals/title"/>
                </h3>

                <table class="form">
                    <xsl:for-each select="additionalsFields/item">
                        <tr>
                            <th>
                                <xsl:value-of select="@name"/>
                            </th>
                            <td>
                                <input type="text" name="additionalField[{@id}]"/>
                            </td>
                        </tr>
                    </xsl:for-each>
                </table>
            </xsl:if>

            <h3>
                <xsl:value-of select="$locale/announcements/adm/forms/eventDescription"/>
            </h3>

            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/shortDescription"/>
                    </th>
                    <td>
                        <textarea name="shortDescription" cols="" rows="10">

                        </textarea>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/description"/>
                    </th>
                    <td>
                        <textarea name="description" editor="Full" cols="" rows="">

                        </textarea>
                    </td>
                </tr>
            </table>

            <input type="submit" value="{$locale/announcements/adm/forms/addEvent}"/>
        </form>

    </xsl:template>
</xsl:stylesheet>