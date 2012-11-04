<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="CMSMenuItems">
		<h2>
			<a href="/adm/cms/items">
				<xsl:value-of select="$locale/cms/adm/menu/h2"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cms/adm/items/h2"/>
		</h2>
		<a href="/adm/cms/items/add/{@id}" class="button">
			<xsl:value-of select="$locale/cms/adm/items/new"/>
		</a>
		<h3>
			<xsl:value-of select="$locale/cms/adm/items/list"/>
		</h3>

		<xsl:choose>
			<xsl:when test="menuitem">
				<table>
					<tr>
						<th>
							<xsl:value-of select="$locale/cms/adm/menuitem/title"/>
						</th>
						<th>
							<xsl:value-of select="$locale/cms/adm/menuitem/type"/>
						</th>
						<th>
							<xsl:value-of select="$locale/cms/adm/menuitem/content"/>
						</th>
						<th>
							<xsl:value-of select="$locale/cms/adm/actions"/>
						</th>
					</tr>
					<xsl:apply-templates select="menuitem"/>
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
						Непонятный элемент
					</td>
				</xsl:otherwise>
			</xsl:choose>
			<td>
				<!-- TODO: заменить эти кнопочки на вызовы шаблонов -->
				<a href="/adm/cms/items/edit/{@id}" class="action edit">
					<xsl:value-of select="$locale/adm/actions/edit"/>
				</a>
				<a href="#" onclick="ajaxer.query('/adm/cms/items/up/{@id}')" class="action up">
					<xsl:if test="position()=1">
						<xsl:attribute name="class">action up disabled</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="$locale/adm/actions/up"/>
				</a>
				<a href="#" onclick="ajaxer.query('/adm/cms/items/down/{@id}')" class="action down">
					<xsl:if test="position()=last()">
						<xsl:attribute name="class">action down disabled</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="$locale/adm/actions/down"/>
				</a>
				<a href="#" onclick="ajaxer.query('/adm/cms/items/delete/{@id}')" class="action delete">
					<xsl:value-of select="$locale/adm/actions/delete"/>
				</a>
			</td>
		</tr>
	</xsl:template>
</xsl:stylesheet>
