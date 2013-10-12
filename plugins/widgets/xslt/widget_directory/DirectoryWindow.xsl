<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="DirectoryWindow">
		<form action="{/DirectoryWindow/@controllerUri}/add" method="post" class="ajaxer" id="DirectoryWindow">
			<table class="widget-text-action">
				<tr>
					<td>
						<input type="search" name="search" id="DirectoryWindowSearch" incremental="incremental" class="full-width"
						       onsearch="directorySearch(this);"/>
					</td>
					<td>
						<a href="#" class="action add submit"/>
					</td>
				</tr>
			</table>
			<table class="widget-text-action scrollable searchable">
				<xsl:apply-templates select="WidgetsDirectoryList/WidgetsDirectory" mode="widget"/>
			</table>
		</form>
	</xsl:template>

	<xsl:template match="WidgetsDirectory" mode="widget">
		<tr>
			<td>
				<a href="{/DirectoryWindow/@controllerUri}/choose/{@id}" class="ajaxer dashed search-me">
					<xsl:value-of select="@name"/>
				</a>
			</td>
			<td>
				<a href="{/DirectoryWindow/@controllerUri}/delete/{@id}" class="action delete ajaxer"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>