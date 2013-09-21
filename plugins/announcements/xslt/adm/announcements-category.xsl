<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsCategory">

		<h2>
			<xsl:value-of select="$locale/announcements/adm/category/title"/>
		</h2>

		<h3>
			<xsl:value-of select="$locale/announcements/adm/category/addCategory"/>
		</h3>

		<form class="ajaxer addCategoryForm" action="/adm/announcements/category/save/" method="post">
			<table class="addCategoryForm">
				<colgroup>
					<col style="width: 180px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/category/name"/>
					</th>
					<td>
						<input type="text" name="name" class="full-width"/>
						<span class="small grey">
							<xsl:value-of
								select="$locale/announcements/adm/category/catNameExample"/>
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
							<xsl:value-of
								select="$locale/announcements/adm/category/techAliasExample"/>
						</span>
					</td>
				</tr>
			</table>
			<div class="form-buttons">
				<input type="submit" value="{$locale/adm/save}"/>
			</div>
		</form>

		<h3>
			<xsl:value-of select="$locale/announcements/adm/category/list"/>
		</h3>

		<xsl:if test="category">
			<table id="announcements-categoryList">
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
						<xsl:value-of select="$locale/announcements/adm/category/name"/>
					</th>
					<th>
						<xsl:value-of select="$locale/announcements/adm/category/techAlias"/>
					</th>
					<th>
					</th>
				</tr>

				<xsl:for-each select="category">
					<colgroup>
						<col style="width: 50px"/>
						<col/>
						<col style="width: 300px"/>
						<col style="width: 70px"/>
					</colgroup>
					<tr>
						<td>
							<xsl:value-of select="position()"/>
						</td>
						<td>
							<div id="ann-category-{@id}">
								<xsl:value-of select="@name"/>
							</div>
							<div id="ann-category-{@id}-edit" class="no-display">
								<form class="ajaxer"
								      action="/adm/announcements/category/save/"
								      method="post">
									<div class="container categoryAdd">
										<input type="hidden" name="catId"
										       value="{@id}"/>
										<input type="hidden"
										       name="originalAlias"
										       value="{@category}"/>
										<input type="text" name="categoryName"
										       value="{@name}"/>
										<xsl:text> / </xsl:text>
										<input type="text" name="categoryAlias"
										       value="{@category}"/>
										<input type="submit"
										       value="{$locale/adm/save}"/>
										<div class="invalid">
											<div class="invalid-text"/>
										</div>
									</div>
								</form>
							</div>
						</td>
						<td>
							<xsl:value-of select="@category"/>
						</td>
						<td class="actions">
							<a href="#" class="action edit"
							   onclick="announcementsUI.editCategory( {@id} );"/>
							<a href="/adm/announcements/category/delete/{@id}/"
							   class="action delete ajaxer"/>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>

	</xsl:template>
</xsl:stylesheet>