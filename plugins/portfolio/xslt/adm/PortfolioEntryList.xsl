<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template match="PortfolioEntryList">
		<h2>
			<xsl:value-of select="$locale/portfolio/adm/list/title"/>
		</h2>
		<a href="/adm/content/portfolio/add" class="action add"/>
		<xsl:choose>
			<xsl:when test="not(PortfolioEntry)">
				<xsl:value-of select="$locale/portfolio/adm/list/empty"/>
			</xsl:when>
			<xsl:otherwise>
				<table>
					<xsl:apply-templates select="PortfolioEntry" mode="adm-list"/>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="PortfolioEntry" mode="adm-list">
		<tr>
			<td>
				<xsl:value-of select="@name"/>
			</td>
			<td>
				<a href="/adm/content/portfolio/edit/{@id}" class="action edit"/>
				<a href="/adm/content/portfolio/delete/{@id}" class="action delete ajaxer"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>