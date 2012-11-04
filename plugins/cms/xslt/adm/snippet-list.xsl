<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="snippetList">
		<h2><xsl:value-of select="$locale/cms/adm/snippet/title"/></h2>
		<xsl:if test="/root/@debug=1">
			<a href="/adm/cms/snippets/add" class="button"><xsl:value-of select="$locale/adm/actions/add"/></a>
		</xsl:if>
		<table>
			<tr>
				<xsl:if test="/root/@debug=1">
					<th>
						<xsl:value-of select="$locale/cms/adm/snippet/name"/>
					</th>
				</xsl:if>
				<th>
					<xsl:value-of select="$locale/cms/adm/snippet/description"/>
				</th>
				<th>
					<xsl:value-of select="$locale/adm/actions/title"/>
				</th>
			</tr>
			<xsl:apply-templates select="snippet" mode="adm"/>
		</table>
	</xsl:template>

	<xsl:template match="snippet" mode="adm">
		<tr>
			<xsl:if test="/root/@debug=1">
				<td>
					<xsl:value-of select="@name"/>
				</td>
			</xsl:if>
			<td>
				<xsl:value-of select="@description"/>
			</td>
			<td>
				<xsl:call-template name="actionEdit">
					<xsl:with-param name="link" select="concat('/adm/cms/snippets/edit/',@id)"/>
				</xsl:call-template>
				<xsl:if test="/root/@debug=1">
					<xsl:call-template name="actionDelete">
						<xsl:with-param name="link" select="concat('/adm/cms/snippets/del/',@id)"/>
					</xsl:call-template>
				</xsl:if>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>