<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<!-- Шаблон добавления элемента -->
	<xsl:template match="CatalogItemAdd">
		<h2>
			<a href="/adm/catalog/categories">
				<xsl:value-of select="$locale/catalog/adm/title-categories"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/catalog/items/category/{@category}">
				<xsl:value-of select="$locale/catalog/adm/title-items"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/title-item-add"/>
		</h2>
		<form action="/adm/catalog/items/save" class="ajaxer">
			<xsl:call-template name="CatalogItemAddImages"/>
			<xsl:call-template name="CatalogItemForm-Main"/>
			<xsl:call-template name="CatalogItemForm-Exts"/>
			<xsl:call-template name="CatalogItemForm-Description"/>
			<input type="submit" class="large_spacing" value="{$locale/catalog/adm/save}"/>
		</form>
	</xsl:template>

	<!-- Шаблон редактирования элемента -->
	<xsl:template match="CatalogItemEdit">
		<h2>
			<a href="/adm/catalog/categories">
				<xsl:value-of select="$locale/catalog/adm/title-categories"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/catalog/items/category/{@category}">
				<xsl:value-of select="$locale/catalog/adm/title-items"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/title-item-edit"/>
		</h2>
		<xsl:call-template name="CatalogItemEditImages"/>
		<form action="/adm/catalog/items/save" class="ajaxer">
			<input type="hidden" name="id" value="{item/@id}"/>
			<xsl:call-template name="CatalogItemForm-Main"/>
			<xsl:call-template name="CatalogItemForm-Exts"/>
			<xsl:call-template name="CatalogItemForm-Description"/>
			<input type="submit" class="large_spacing" value="{$locale/catalog/adm/save}"/>
		</form>
	</xsl:template>

	<!-- Редактирование изображений -->
	<xsl:template name="CatalogItemEditImages">
		<h3>
			<xsl:value-of select="$locale/catalog/adm/images/title"/>
		</h3>
		<form class="ajaxer" action="/adm/catalog/items/addimage">
			<xsl:value-of select="$locale/catalog/adm/item/add-images"/>
			<input type="hidden" name="id" value="{item/@id}"/>
			<input type="file" name="images[]" multiple="multiple" onchange="$(this).parent().submit()"/>
		</form>
		<br/>
		<xsl:for-each select="item/image">
			<xsl:sort select="@main" order="descending"/>
			<div>
				<xsl:choose>
					<xsl:when test="@main=1">
						<xsl:attribute name="class">item-image main-image</xsl:attribute>
						<div class="img" style="background-image: url('/catalog/items/{@id}m.png')" />
						<div class="controls">
							<span>
								<xsl:value-of select="$locale/catalog/adm/images/main"/>
							</span>
						</div>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="class">item-image</xsl:attribute>
						<div class="img" style="background-image: url('/catalog/items/{@id}m.png')"/>
						<div class="controls">
							<a href="/adm/catalog/items/setmainimage/{../@id}/{@id}" class="ajaxer action">
								<xsl:value-of select="$locale/catalog/adm/images/setmain"/>
							</a>
							<a href="/adm/catalog/items/deleteimage/{../@id}/{@id}" class="ajaxer action delete">
								<xsl:value-of select="$locale/catalog/adm/images/delete"/>
							</a>
						</div>
					</xsl:otherwise>
				</xsl:choose>
			</div>
		</xsl:for-each>
	</xsl:template>

	<!-- Добавление картинок -->
	<xsl:template name="CatalogItemAddImages">
		<h3>
			<xsl:value-of select="$locale/catalog/adm/images/title"/>
		</h3>
		<table class="form">
			<tr>
				<th>
					<xsl:value-of select="$locale/catalog/adm/item/main-image"/>
				</th>
				<td>
					<input type="file" name="mainImage"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/catalog/adm/item/more-images"/>
				</th>
				<td>
					<a href="#" class="action"
					   onclick="$(this).before('&lt;input type=&quot;file&quot; name=&quot;images[]&quot; multiple=&quot;multiple&quot;/&gt;')">
						<xsl:value-of select="$locale/catalog/adm/item/add-image"/>
					</a>
				</td>
			</tr>
		</table>
	</xsl:template>

	<!-- Основные характеристики -->
	<xsl:template name="CatalogItemForm-Main">
		<h3>
			<xsl:value-of select="$locale/catalog/adm/item/options"/>
		</h3>
		<table class="form">
			<tr>
				<th>
					<xsl:value-of select="$locale/catalog/adm/category"/>
				</th>
				<td>
					<select name="category">
						<xsl:choose>
							<xsl:when test="@category">
								<xsl:call-template name="CatalogCategorySelect">
									<xsl:with-param name="selected" select="@category"/>
								</xsl:call-template>
							</xsl:when>
						</xsl:choose>
					</select>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/catalog/adm/item/name"/>
				</th>
				<td>
					<input type="text" name="name" value="{item/@name}"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/catalog/adm/item/price"/>
				</th>
				<td>
					<input type="number" name="price" value="{item/@price}" step="0.01"/>
				</td>
			</tr>
			<tr>
				<th>
					<xsl:value-of select="$locale/catalog/adm/item/visible"/>
				</th>
				<td>
					<input type="checkbox" name="visible">
						<xsl:if test="not(@visible=0)">
							<xsl:attribute name="checked">checked</xsl:attribute>
						</xsl:if>
					</input>
				</td>
			</tr>
		</table>
	</xsl:template>

	<!-- Описание -->
	<xsl:template name="CatalogItemForm-Description">
		<h3>
			<xsl:value-of select="$locale/catalog/adm/item/description"/>
		</h3>
		<textarea name="description" editor="Full">
			<xsl:value-of select="item/@description"/>
		</textarea>
	</xsl:template>

	<!-- Дополнительные характеристики -->
	<xsl:template name="CatalogItemForm-Exts">
		<xsl:if test="ext">
			<h3>
				<xsl:value-of select="$locale/catalog/adm/item/exts"/>
			</h3>
			<table class="form">
				<xsl:for-each select="ext">
					<xsl:variable name="extId" select="@id"/>
					<tr>
						<th>
							<xsl:value-of select="@name"/>
						</th>
						<td>
							<xsl:choose>
								<xsl:when test="@set=0">
									<input type="text" name="ext[{$extId}]">
										<xsl:attribute name="value">
											<xsl:value-of select="../item/ext[@id=$extId]/@value"/>
										</xsl:attribute>
									</input>
								</xsl:when>
								<xsl:when test="set">
									<xsl:for-each select="set">
										<xsl:variable name="setId" select="@id"/>
										<div style="display:inline-block">
											<input type="checkbox"
											       name="ext[{$extId}][{$setId}]">
												<xsl:if test="../../item/ext[@id=$extId]/set[@id=$setId]">
													<xsl:attribute name="checked">
														<xsl:text>checked</xsl:text>
													</xsl:attribute>
												</xsl:if>
											</input>
											<!--
											<xsl:if test="../@withImages=1">
												<img src="/catalog/ext/{@id}.png" alt=""/>
											</xsl:if>
											-->
											<xsl:value-of select="@name"/>
										</div>
									</xsl:for-each>
								</xsl:when>
							</xsl:choose>
						</td>
					</tr>
				</xsl:for-each>
			</table>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>