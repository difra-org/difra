<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/content/CatalogConfig">
		<h2>
			<xsl:value-of select="$locale/catalog/adm/config/title"/>
		</h2>
		<form action="/adm/catalog/config/save" class="ajaxer">
			<h3>
				<xsl:value-of select="$locale/catalog/adm/config/title-secondary"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 390px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/config/maxdepth"/>
					</th>
					<td>
						<input type="number" name="maxdepth" value="{@maxdepth}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/config/perpage"/>
					</th>
					<td>
						<input type="number" name="perpage" value="{@perpage}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/config/hide-empty-categories"/>
					</th>
					<td>
						<input type="checkbox" name="hideempty" value="1">
							<xsl:if test="@hideempty=1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/config/allow-no-main-image"/>
					</th>
					<td>
						<input type="checkbox" name="allownoimage" value="1">
							<xsl:if test="@allownoimage=1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/config/use-scaleandcrop"/>
					</th>
					<td>
						<input type="checkbox" name="usescale" value="1">
							<xsl:if test="@usescale=1">
								<xsl:attribute name="checked">checked</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>
			<h3>
				<xsl:value-of select="$locale/catalog/adm/config/image-settings"/>
			</h3>
			<table class="form">
				<colgroup>
					<col style="width: 390px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/catalog/adm/config/image-sizes" disable-output-escaping="yes"/>
					</th>
					<td>
						<textarea rows="5" cols="25" name="imgSizes">
							<xsl:value-of select="@imgSizes"/>
						</textarea>
						<div class="small grey">
							<xsl:value-of select="$locale/catalog/adm/config/image-sizes-info" disable-output-escaping="yes"/>
						</div>
					</td>
				</tr>
			</table>
			<div class="form-buttons">
				<input type="submit" value="{$locale/adm/save}"/>
			</div>

		</form>
	</xsl:template>
</xsl:stylesheet>