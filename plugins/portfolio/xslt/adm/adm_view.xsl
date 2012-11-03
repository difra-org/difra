<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-view">
		<h2>
			<xsl:value-of select="$locale/view/title"/>
		</h2>
		<a href="/adm/portfolio/load" class="button">
			Загрузка работы в портфолио
		</a>
		<h3>
			Элементы портфолио
		</h3>
		<table>
			<tr>
				<th>Название</th>
				<th>Дата релиза</th>
				<th>Действия</th>
			</tr>
			<xsl:for-each select="/root/portfolio/item">
			<tr>
				<td>
					<xsl:value-of select="@name"/>
				</td>
				<td>
					<xsl:value-of select="@release_date"/>
				</td>
				<td>
					<a href="/adm/portfolio/showimages/id/{@id}/" target="_blank" class="action view">
						<xsl:value-of select="$locale/cms/adm/view"/>
					</a>

                    <a href="/adm/portfolio/edit/id/{@id}/" class="action edit">edit</a>
                    <a href="/adm/portfolio/delete/id/{@id}/" class="action delete ajaxer">delete</a>

				</td>
			</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>

