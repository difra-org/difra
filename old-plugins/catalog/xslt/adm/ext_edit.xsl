<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="CatalogExtAdd">
		<h2>
			<a href="/adm/catalog/ext">
				<xsl:value-of select="$locale/catalog/adm/ext/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/ext/title-add"/>
		</h2>
		<xsl:call-template name="CatalogExtForm"/>
	</xsl:template>

	<xsl:template match="CatalogExtEdit">
		<h2>
			<a href="/adm/catalog/ext">
				<xsl:value-of select="$locale/catalog/adm/ext/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/ext/title-edit"/>
		</h2>
		<xsl:call-template name="CatalogExtForm"/>
	</xsl:template>

	<xsl:template name="CatalogExtForm">
		<h3>
			<xsl:value-of select="$locale/catalog/adm/ext/options"/>
		</h3>
		<form action="/adm/catalog/ext/save" method="post" class="ajaxer">
			<input type="hidden" name="group">
				<xsl:choose>
					<xsl:when test="@group">
						<xsl:attribute name="value">
							<xsl:value-of select="@group"/>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="value">
							<xsl:text>0</xsl:text>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</input>
			<xsl:if test="@id">
				<input type="hidden" name="id" value="{@id}"/>
			</xsl:if>
			<table class="form">
				<colgroup>
					<col style="width: 150px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/ext/name"/>
					</th>
					<td>
						<input type="text" name="name" value="{@name}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/ext/type"/>
					</th>
					<td>
						<input type="radio" name="set" value="0" id="set_0"
						       onclick="catalog.extWarning({@set},0)">
							<xsl:if test="@set=0 or not(@set)">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
						<label for="set_0">
							<xsl:value-of select="$locale/catalog/adm/ext/not-set"/>
						</label>
						<input type="radio" name="set" value="1" id="set_1"
						       onclick="catalog.extWarning({@set},1)">
							<xsl:if test="@set=1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
						<label for="set_1">
							<xsl:value-of select="$locale/catalog/adm/ext/is-set"/>
						</label>
						<input type="radio" name="set" value="2" id="set_2"
						       onclick="catalog.extWarning({@set},2)">
							<xsl:if test="@set=2">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
						<label for="set_2">
							<xsl:value-of select="$locale/catalog/adm/ext/set-with-images"/>
						</label>
					</td>
				</tr>
			</table>
			<script type="text/javascript">
				catalog.warnings = {
					change12:"<xsl:value-of select="$locale/catalog/adm/ext/warning12"/>",
					change21:"<xsl:value-of select="$locale/catalog/adm/ext/warning21"/>",
					change20:"<xsl:value-of select="$locale/catalog/adm/ext/warning20"/>",
					change10:"<xsl:value-of select="$locale/catalog/adm/ext/warning10"/>"
				}
			</script>
			<div id="extWarning" class="warning" style="display:none"/>
			<div class="form-buttons">
				<input type="submit" value="{$locale/catalog/adm/ext/save}"/>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>