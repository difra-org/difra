<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-contributors">
		<h2><xsl:value-of select="$locale/contributors/title"/></h2>
		<a href="#" id="addButton" onclick="javascript: showAddContributor();" class="button">
            <xsl:value-of select="$locale/addContributor"/>
		</a>

		<h3><xsl:value-of select="$locale/contributorsList"/></h3>
		<table>
			<tr>
				<th><xsl:value-of select="$locale/contributors/labels/id"/></th>
				<th><xsl:value-of select="$locale/contributors/labels/name"/></th>
				<th><xsl:value-of select="$locale/contributors/labels/email"/></th>
				<th><xsl:value-of select="$locale/contributors/labels/flags"/></th>
				<th><xsl:value-of select="$locale/contributors/labels/roles"/></th>
				<th><xsl:value-of select="$locale/contributors/labels/actions"/></th>
			</tr>
			
			<xsl:for-each select="/root/contributors/item">
				<tr>
					<td>
						<xsl:value-of select="@id"/>
					</td>
					<td>
						<xsl:value-of select="@name"/>
					</td>
					<td>
						<xsl:value-of select="@email"/>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="@archive=1">
								<xsl:value-of select="$locale/loadWork/labels/archive"/>
							</xsl:when>
						</xsl:choose>
					</td>
					<td>
						<xsl:value-of select="@role"/>
					</td>
					<td class="actions">

                        <a href="/adm/portfolio/editcontributor/id/{@id}/" class="action edit"/>
                        <a href="/adm/portfolio/delcontributor/id/{@id}/" class="action delete"/>

					</td>
				</tr>
			</xsl:for-each>
		</table>

        <div id="addContributor">
            <br/>
            <form action="/adm/portfolio/savecontributor/" name="savecontributor" id="savecontributor"
                  enctype="multipart/form-data" method="post" class="ajaxer">
                <fieldset>
                    <legend>
                        <xsl:value-of select="$locale/contributors/add_legend"/>
                    </legend>
                    <label>
                        <xsl:value-of select="$locale/contributors/labels/chooseUser"/>
                    </label>
                    <select id="user" name="user">
                        <option value="0" selected="selected">
                            <xsl:value-of select="$locale/contributors/labels/notSelected"/>
                        </option>
                        <xsl:for-each select="/root/users/item">
                            <option value="{@id}">
                                <xsl:value-of select="@email"/>
                            </option>
                        </xsl:for-each>
                    </select>
                    <table>
                        <tr>
                            <td>
                                <label>
                                    <xsl:value-of select="$locale/contributors/labels/name"/>
                                </label>
                                <input name="name" id="name" type="text"/>
                                <br/>
                            </td>
                            <td>
                                <label>
                                    <xsl:value-of select="$locale/contributors/labels/linkText"/>
                                </label>
                                <input name="linktext" id="linkText" type="text"/>
                                <br/>
                            </td>
                        </tr>
                    </table>
                    <label>
                        <xsl:value-of select="$locale/contributors/labels/defaultRole"/>
                    </label>
                    <input name="role" id="role" type="text"/>
                    <br/>
                </fieldset>
                <input type="submit" id="sendContributor" value="{$locale/addContributor}"/>
            </form>
        </div>

	</xsl:template>
</xsl:stylesheet>
