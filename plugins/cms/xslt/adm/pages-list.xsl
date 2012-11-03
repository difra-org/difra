<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="CMSList">
		<h2><xsl:value-of select="$locale/cms/adm/h2"/></h2>
		<a href="/adm/cms/add" class="button"><xsl:value-of select="$locale/cms/adm/newpage"/></a>
		<h3><xsl:value-of select="$locale/cms/adm/pageslist"/></h3>

		<xsl:choose>
			<xsl:when test="page">
				<table>
					<tr>
						<th><xsl:value-of select="$locale/cms/adm/name"/></th>
						<th><xsl:value-of select="$locale/cms/adm/uri"/></th>
						<th><xsl:value-of select="$locale/cms/adm/hidden"/></th>
						<th><xsl:value-of select="$locale/cms/adm/actions"/></th>
					</tr>
					<xsl:apply-templates select="page"/>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<span class="message">
					<xsl:value-of select="$locale/cms/adm/no-pages"/>
				</span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="page">
		<tr>
			<td>
				<xsl:value-of select="@title"/>
			</td>
			<td>
				<xsl:value-of select="@uri"/>
			</td>
			<td>
				<xsl:choose>
					<xsl:when test="@hidden=1">
						<xsl:value-of select="$locale/cms/adm/hidden-flag"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>—</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
			</td>
			<td>
				<!-- TODO: заменить эти кнопочки на вызовы шаблонов -->
				<a href="{@uri}" target="_blank" class="action view">
					<xsl:value-of select="$locale/cms/adm/view"/>
				</a>
				<a href="/adm/cms/edit/{@id}" class="action edit">
					<xsl:value-of select="$locale/cms/adm/edit"/>
				</a>
				<a href="#" onclick="ajaxer.query('/adm/cms/delete/{@id}')" class="action delete">
					<xsl:value-of select="$locale/cms/adm/delete"/>
				</a>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>
