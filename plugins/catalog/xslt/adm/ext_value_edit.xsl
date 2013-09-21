<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="CatalogExtValueAdd">
		<h2>
			<a href="/adm/catalog/ext">
				<xsl:value-of select="$locale/catalog/adm/ext/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/catalog/ext/values/{@ext}">
				<xsl:value-of select="$locale/catalog/adm/ext/title-values"/>
				<xsl:text> «</xsl:text>
				<xsl:value-of select="@extName"/>
				<xsl:text>»</xsl:text>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/ext/value-new-title"/>
		</h2>
		<xsl:call-template name="CatalogExtValueForm"/>
	</xsl:template>

	<xsl:template match="CatalogExtValueEdit">
		<h2>
			<a href="/adm/catalog/ext">
				<xsl:value-of select="$locale/catalog/adm/ext/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/catalog/ext/values/{@ext}">
				<xsl:value-of select="$locale/catalog/adm/ext/title-values"/>
				<xsl:text> «</xsl:text>
				<xsl:value-of select="@extName"/>
				<xsl:text>»</xsl:text>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/ext/value-edit-title"/>
			<xsl:text> «</xsl:text>
			<xsl:value-of select="@name"/>
			<xsl:text>»</xsl:text>
		</h2>
		<xsl:call-template name="CatalogExtValueForm"/>
	</xsl:template>

	<xsl:template name="CatalogExtValueForm">
		<h3>
			<xsl:value-of select="$locale/catalog/adm/ext/options"/>
		</h3>
		<form action="/adm/catalog/ext/values/save" method="post" class="ajaxer">
			<xsl:if test="@ext">
				<xsl:attribute name="enctype">multipart/form-data</xsl:attribute>
				<input type="hidden" name="ext" value="{@ext}"/>
			</xsl:if>
			<xsl:if test="@id">
				<input type="hidden" name="id" value="{@id}"/>
			</xsl:if>
			<table class="form">
				<colgroup>
					<col style="width: 120px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/ext/value-name"/>
					</th>
					<td>
						<input type="text" name="value" value="{@name}" class="full-width"/>
					</td>
				</tr>
				<xsl:if test="@set=2">
					<tr>
						<th>
							<xsl:value-of select="$locale/catalog/adm/ext/value-image"/>
						</th>
						<td>
							<input type="file" name="image"/>
						</td>
					</tr>
				</xsl:if>
			</table>
			<div class="form-buttons">
				<input type="submit" value="{$locale/catalog/adm/ext/save}"/>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>