<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsAdditionals">

		<h2>
			<xsl:value-of select="$locale/announcements/adm/additionals/title"/>
		</h2>

		<h3>
			<xsl:value-of select="$locale/announcements/adm/additionals/addNewField"/>
		</h3>

		<form class="ajaxer" method="post" action="/adm/announcements/additionals/save">

			<table class="addCategoryForm">
				<colgroup>
					<col style="width: 180px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/additionals/fieldName"/>
					</th>
					<td>
						<input type="text" name="name" class="full-width"/>
						<span class="small grey">
							<xsl:value-of
								select="$locale/announcements/adm/additionals/nameExample"/>
						</span>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/category/techAlias"/>

					</th>
					<td>
						<input type="text" name="alias" class="full-width"/>
						<span class="small grey">
							<xsl:value-of select="$locale/announcements/adm/additionals/techAliasExample"/>
						</span>
					</td>
				</tr>
			</table>
			<div class="form-buttons">
				<input type="submit" value="{$locale/adm/save}"/>
			</div>
		</form>

		<h3>
			<xsl:value-of select="$locale/announcements/adm/additionals/list"/>
		</h3>

		<xsl:if test="item">
			<table class="form">
				<colgroup>
					<col style="width: 50px"/>
					<col/>
					<col style="width: 300px"/>
					<col style="width: 70px"/>
				</colgroup>
				<tr>
					<th>
					</th>
					<th>
						<xsl:value-of select="$locale/announcements/adm/additionals/fieldName"/>
					</th>
					<th>
						<xsl:value-of select="$locale/announcements/adm/category/techAlias"/>
					</th>
					<th>
					</th>
				</tr>

				<xsl:for-each select="item">
					<tr>
						<td>
							<xsl:value-of select="position()"/>
						</td>
						<td>
							<div id="addField-{@id}">
								<xsl:value-of select="@name"/>
							</div>

							<div id="addField-{@id}-edit"
							     class="addCategoryForm no-display">
								<form class="ajaxer"
								      action="/adm/announcements/additionals/save/"
								      method="post">
									<div class="container categoryAdd">
										<input type="hidden" name="id"
										       value="{@id}"/>
										<input type="hidden"
										       name="originalAlias"
										       value="{@alias}"/>
										<input type="text" name="name"
										       value="{@name}"/>
										<xsl:text> / </xsl:text>
										<input type="text" name="alias"
										       value="{@alias}"/>
										<input type="submit"
										       value="{$locale/adm/save}"/>
									</div>
								</form>
							</div>

						</td>
						<td>
							<xsl:value-of select="@alias"/>
						</td>
						<td class="actions">
							<a href="#" class="action edit"
							   onclick="announcementsUI.editAdditionals( {@id} );"/>
							<a href="/adm/announcements/additionals/delete/{@id}/"
							   class="action delete ajaxer"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>

		</xsl:if>

	</xsl:template>
</xsl:stylesheet>