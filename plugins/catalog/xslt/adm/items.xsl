<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="CatalogItemsList">
		<h2>
			<a href="/adm/catalog/categories">
				<xsl:value-of select="$locale/catalog/adm/title-categories"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/title-items"/>
		</h2>
		<h3>
			<xsl:value-of select="$locale/catalog/adm/title-filter-items"/>
		</h3>

			<select name="parent" onchange="catalog.switchCategory(this)">
				<xsl:call-template name="CatalogCategorySelect">
					<xsl:with-param name="selected" select="@selected"/>
				</xsl:call-template>
			</select>
		<h3>
			<xsl:value-of select="$locale/catalog/adm/title-items-list"/>
		</h3>
		<a href="/adm/catalog/items/add/to/{@selected}" class="button">
			<xsl:value-of select="$locale/catalog/adm/new"/>
		</a>
		<br/>
		<xsl:choose>
			<xsl:when test="item">
				<br/>
				<table>
					<tr>
						<th>
							<xsl:value-of select="$locale/catalog/adm/item/name"/>
						</th>
						<th>
							<xsl:value-of select="$locale/catalog/adm/item/price"/>
						</th>
						<th>
							<xsl:value-of select="$locale/catalog/adm/item/flags"/>
						</th>
						<th>
							<xsl:value-of select="$locale/adm/actions/title"/>
						</th>
					</tr>
					<xsl:for-each select="item">
						<tr>
							<td>
								<xsl:value-of select="@name"/>
							</td>
							<td>
								<xsl:value-of select="@humanprice"/>
							</td>
							<td>
								<xsl:if test="@visible=0">
									<xsl:value-of select="$locale/catalog/adm/item/invisible"/>
								</xsl:if>
							</td>
							<td>
								<xsl:call-template name="actionEdit">
									<xsl:with-param name="link">
										<xsl:text>/adm/catalog/items/edit/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
								<xsl:call-template name="actionDelete">
									<xsl:with-param name="link">
										<xsl:text>/adm/catalog/items/delete/</xsl:text>
										<xsl:value-of select="@id"/>
									</xsl:with-param>
								</xsl:call-template>
							</td>

						</tr>
					</xsl:for-each>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<div class="message middle_spacing">
					<xsl:value-of select="$locale/catalog/adm/no-items"/>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>