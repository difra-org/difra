<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="DirectoryWindow">
		<form action="{/root/@controllerUri}/add" method="post" class="ajaxer">
			<table class="widget-text-action">
				<tr>
					<td>
						<input type="search" name="search" class="full-width"/>
					</td>
					<td>
						<a href="#" class="action add submit"/>
					</td>
				</tr>
			</table>
			<table class="widget-text-action scrollable">
				<xsl:apply-templates select="WidgetsDirectoryList/WidgetsDirectory" mode="widget"/>
			</table>
		</form>
	</xsl:template>

	<xsl:template match="WidgetsDirectory" mode="widget">
		<tr>
			<td>
				<xsl:value-of select="@name"/>
			</td>
			<td>
				<a href="{/root/@controllerUri}/{@id}" class="action delete"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>