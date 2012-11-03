<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-loadWork">
		<h2>
			<a href="/adm/portfolio">
				<xsl:value-of select="$locale/view/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/loadWork/title"/>
		</h2>

		<form action="/adm/portfolio/savework/" name="loadwork" id="loadWork" enctype="multipart/form-data" method="post" class="ajaxer">
			<h3>
                <xsl:value-of select="$locale/loadWork/labels/images"/>
			</h3>
			<table class="form" id="addImageList">
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/mainImage"/>
					</th>
					<td>
						<input name="workImage" id="workImage" type="file"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/previewImage"/>
					</th>
					<td>
						<input name="previewImage" id="previewImage" type="file"/>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/loadWork/workInfo"/>
			</h3>
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/name"/>

					</th>
					<td>
						<input name="name" type="text"/>
					</td>
				</tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/loadWork/labels/date"/>
                    </th>
                    <td>
                        <input name="date" type="text"/>
                    </td>
                </tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/url"/>
					</th>
					<td>
						<input name="workurl" type="text" />
					</td>
				</tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/loadWork/labels/linkText"/>
                    </th>
                    <td>
                        <input name="linkText" type="text"/>
                    </td>
                </tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/software"/>
					</th>
					<td>
						<input name="software" type="text"/>
					</td>
				</tr>

			</table>
			<h3>
				<xsl:value-of select="$locale/loadWork/users"/>
			</h3>

			<table class="form" id="usersSelect">

			</table>

			<table class="form">
				<tr>
					<td>
						<select name="u" id="u">
							<xsl:for-each select="/root/contributors/item">
								<option value="{@id}">
									<xsl:value-of select="@name"/> (<xsl:value-of select="@email"/>)
									<xsl:if test="not(@role='')">
										<xsl:text>. </xsl:text><xsl:value-of select="@role"/><xsl:text>.</xsl:text>
									</xsl:if>
									<xsl:if test="@archive=1">
										[<xsl:value-of select="$locale/loadWork/labels/archive"/>]
									</xsl:if>
								</option>
							</xsl:for-each>
						</select>
						<xsl:for-each select="/root/contributors/item">
							<input type="hidden" id="userRole_{@id}" value="{@role}"/>
							<input type="hidden" id="userName_{@id}" value="{@name}"/>
						</xsl:for-each>
					</td>
					<td>
						<a href="#" class="action" onclick="addUserToWork()">
                            <xsl:value-of select="$locale/loadWork/labels/addUser"/>
						</a>
					</td>
				</tr>
			</table>
			<h3>
				<xsl:value-of select="$locale/loadWork/labels/description"/>
			</h3>
			<textarea name="description" editor="Full" />

			<input type="submit" id="sendWork" value="{$locale/loadWork/labels/addWork}"/>
		</form>

	</xsl:template>
</xsl:stylesheet>

