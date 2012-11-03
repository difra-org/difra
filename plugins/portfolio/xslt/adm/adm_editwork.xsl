<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-edit">
		<h2>
			<a href="/adm/portfolio">
				<xsl:value-of select="$locale/view/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/edit/title"/>
		</h2>

		<form action="/adm/portfolio/saveeditwork/id/{id}/" name="editwork" id="editWork" enctype="multipart/form-data" method="post" class="ajaxer">
			<h3>
				<xsl:value-of select="$locale/loadWork/labels/images"/>
			</h3>
			<table class="form" id="addImageList">
				<tr>
					<th>
						<a href="/portimages/portfolio-{id}-large.jpg"
						   title="/portimages/portfolio-{id}-large.jpg">
							<xsl:value-of select="$locale/edit/labels/changePic"/>
						</a>
					</th>
					<td>
						<input name="workImage" id="workImage" type="file"/>
					</td>
				</tr>
				<tr>
					<th>
						<a href="/portimages/portfolio-{id}-small.jpg" title="/portimages/portfolio-{id}-small.jpg">
							<xsl:value-of select="$locale/edit/labels/changePreviewPic"/>
						</a>
					</th>
					<td>
						<input name="previewImage" id="previewImage" type="file" />
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
						<input name="name" id="name" type="text" value="{name}"/>
					</td>
				</tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/loadWork/labels/date"/>
                    </th>
                    <td>
                        <input name="date" id="date" type="text" value="{release_date}"/>
                    </td>
                </tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/url"/>
					</th>
					<td>
						<input name="workurl" id="workUrl" type="text" value="{url}"/>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/linkText"/>
					</th>
					<td>
						<input name="linkText" id="linkText" type="text" value="{url_text}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/loadWork/labels/software"/>
					</th>
					<td>
						<input name="software" id="software" type="text" value="{software}" />
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/loadWork/users"/>
			</h3>

			<table class="form" id="usersSelect">
				<xsl:for-each select="users/item">
					<tr id="user_{@user_id}_{position()}">
						<th>
							<xsl:value-of select="@name"/>
						</th>
						<td>
							<input name="users[]" type="hidden" value="{@user_id}"/>
							<input type="text" name="userRole[]" value="{@role}"/>
						</td>
						<td>
							<a href="#" onclick="delUserFromWork('{@user_id}_{position()}')"
							   class="action delete" />
						</td>
					</tr>
				</xsl:for-each>
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

			<textarea name="description" id="description" editor="Full">
				<xsl:value-of select="description"/>
			</textarea>

			<input type="submit" id="sendWork" value="{$locale/edit/labels/editWork}" />
		</form>

	</xsl:template>
</xsl:stylesheet>