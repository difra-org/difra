<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="announcementsEdit">

        <h2>
            <a href="/adm/announcements/">
                <xsl:value-of select="$locale/announcements/adm/announcements"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/announcements/adm/edit"/>
        </h2>

        <form action="/adm/announcements/update/" class="ajaxer" method="post">

            <input type="hidden" name="id" value="{event/id}"/>

            <h3>
                <xsl:value-of select="$locale/announcements/adm/forms/pic"/>
            </h3>

            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/imagePreview"/>
                        <br/>
                        <img src="/announcements/{event/id}.png"/>
                    </th>
                    <td>
                        <input type="file" name="eventImage" accept="image/jpeg,image/png,image/gif"/>
                    </td>
                </tr>
            </table>

            <h3>
                <xsl:value-of select="$locale/announcements/adm/forms/mainParameters"/>
            </h3>

            <table class="form">

                <xsl:if test="newGroups/group">

                    <tr>
                        <th>
                            <xsl:value-of select="$locale/announcements/adm/forms/group"/>
                        </th>
                        <td>
                            <select name="group">
                                <xsl:for-each select="newGroups/group">
                                    <option value="{@id}">
                                        <xsl:if test="@id=/root/announcementsEdit/event/group">
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
                        <input type="text" name="title" value="{event/title}"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/eventDate"/>
                    </th>
                    <td>
                        <input type="text" name="eventDate" id="eventDate" value="{event/eventDate}"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/beginDate"/>
                    </th>
                    <td>
                        <input type="text" name="beginDate" id="beginDate" disabled="disabled" value="{event/beginDate}"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/endDate"/>
                    </th>
                    <td>
                        <input type="text" name="endDate" id="endDate" disabled="disabled" value="{event/endDate}"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/priority"/>
                    </th>
                    <td>
                        <input type="hidden" id="priorityValue" name="priorityValue" value="{event/priority}"/>
                        <div id="prioritySlider"/>
                        <div id="priorityValueView">
                            <xsl:value-of select="event/priority"/>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/visibility"/>
                    </th>
                    <td>
                        <input type="checkbox" name="visible" value="1">
                                <xsl:if test="event/visible=1">
                                    <xsl:attribute name="checked">
                                        <xsl:text>checked</xsl:text>
                                    </xsl:attribute>
                                </xsl:if>
                        </input>
                    </td>
                </tr>
            </table>

            <h3>
                <xsl:value-of select="$locale/announcements/adm/forms/eventDescription"/>
            </h3>

            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/shortDescription"/>
                    </th>
                    <td>
                        <textarea name="shortDescription" editor="Full">
                            <xsl:value-of select="event/shortDescription" disable-output-escaping="yes"/>
                        </textarea>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/announcements/adm/forms/description"/>
                    </th>
                    <td>
                        <textarea name="description" editor="Full">
                            <xsl:value-of select="event/description" disable-output-escaping="yes"/>
                        </textarea>
                    </td>
                </tr>
            </table>

            <input type="submit" value="{$locale/announcements/adm/forms/saveEvent}"/>
        </form>

    </xsl:template>
</xsl:stylesheet>