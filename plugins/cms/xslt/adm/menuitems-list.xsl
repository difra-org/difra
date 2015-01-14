<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="CMSMenuItems">
		<h2>
			<a href="/adm/content/menu">
				<xsl:value-of select="$locale/cms/adm/menu/h2"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cms/adm/items/h2"/>
		</h2>
		<a href="/adm/content/menu/add/{@id}" class="button">
			<xsl:value-of select="$locale/cms/adm/items/new"/>
		</a>

		<xsl:choose>
			<xsl:when test="menuitem">
				<table>
					<colgroup>
						<col/>
						<col style="width: 220px"/>
						<col style="width: 220px"/>
						<col style="width: 130px"/>
					</colgroup>
					<thead>
						<tr>
							<th>
								<xsl:value-of select="$locale/cms/adm/menuitem/title"/>
							</th>
							<th>
								<xsl:value-of select="$locale/cms/adm/menuitem/type"/>
							</th>
							<th>
								<xsl:value-of
									select="$locale/cms/adm/menuitem/content"/>
							</th>
							<th>
							</th>
						</tr>
					</thead>
					<tbody>
						<xsl:apply-templates select="menuitem"/>
					</tbody>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<span class="message">
					<xsl:value-of select="$locale/cms/adm/items/empty"/>
				</span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="menuitem">
		<tr>
			<xsl:choose>
				<xsl:when test="page">
					<td>
						<xsl:value-of select="page/@title"/>
					</td>
					<td>
						<xsl:value-of select="$locale/cms/adm/menuitem/type-page"/>
					</td>
					<td>
						<xsl:value-of select="page/@uri"/>
					</td>
				</xsl:when>
				<xsl:when test="@link!=''">
					<td>
						<xsl:value-of select="@linkLabel"/>
					</td>
					<td>
						<xsl:value-of select="$locale/cms/adm/menuitem/type-link"/>
					</td>
					<td>
						<xsl:value-of select="@link"/>
					</td>
				</xsl:when>
				<xsl:otherwise>
					<td colspan="3">
						Непонятный элемент.
					</td>
				</xsl:otherwise>
			</xsl:choose>
			<td class="actions">
				<!-- TODO: заменить эти кнопочки на вызовы шаблонов -->
				<a href="/adm/content/menu/edit/{@id}" class="action edit"/>
				<a href="/adm/content/menu/up/{@id}" class="action up ajaxer">
					<xsl:if test="position()=1">
						<xsl:attribute name="class">action up ajaxer disabled</xsl:attribute>
					</xsl:if>
				</a>
				<a href="/adm/content/menu/down/{@id}" class="action down ajaxer">
					<xsl:if test="position()=last()">
						<xsl:attribute name="class">action down ajaxer disabled</xsl:attribute>
					</xsl:if>
				</a>
				<a href="/adm/content/menu/delete/{@id}" class="action delete ajaxer"/>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>
