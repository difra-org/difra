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
					<colgroup>
						<col style="width: 100px"/>
						<col/>
						<col/>
						<col/>
					</colgroup>
					<tr>
						<th></th>
						<th><xsl:value-of select="$locale/portfolio/entry/name"/></th>
						<th><xsl:value-of select="$locale/portfolio/entry/release"/></th>
						<th></th>
					</tr>
					<xsl:apply-templates select="PortfolioEntry" mode="adm-list"/>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="PortfolioEntry" mode="adm-list">

		<xsl:variable name="wId" select="@id"/>

		<tr>
			<td>
				<a href="/adm/content/portfolio/edit/{@id}/">
					<span class="portfolioPreview"
					      style="background-image: url('/portimages/{/root/content/PortfolioEntryList/image[@portfolio=$wId]/@id}-small.png');"/>
				</a>
			</td>
			<td>
				<a href="/adm/content/portfolio/edit/{@id}/">
					<xsl:value-of select="@name"/>
				</a>
			</td>
			<td>
				<xsl:value-of select="@release"/>
			</td>
			<td>
				<a href="/adm/content/portfolio/edit/{@id}" class="action edit"/>
				<a href="/adm/content/portfolio/delete/{@id}" class="action delete ajaxer"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>