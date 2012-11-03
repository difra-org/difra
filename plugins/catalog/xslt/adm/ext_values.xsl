<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="CatalogExtValueList">
		<h2>
			<a href="/adm/catalog/ext">
				<xsl:value-of select="$locale/catalog/adm/ext/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/catalog/adm/ext/title-values"/>
			<xsl:text> «</xsl:text>
			<xsl:value-of select="@name" />
			<xsl:text>»</xsl:text>
		</h2>
		<a href="/adm/catalog/ext/values/add/to/{@id}" class="button">
			<xsl:value-of select="$locale/catalog/adm/ext/value-new"/>
		</a>
		<h3>
			<xsl:value-of select="$locale/catalog/adm/ext/title-values-list"/>
		</h3>
		<xsl:choose>
			<xsl:when test="set">
				<table>
					<thead>
						<tr>
							<xsl:if test="@setImages=1">
								<th><xsl:value-of select="$locale/catalog/adm/ext/value-image"/></th>
							</xsl:if>
							<th><xsl:value-of select="$locale/catalog/adm/ext/value-name"/></th>
							<th><xsl:value-of select="$locale/adm/actions/title"/></th>
						</tr>
					</thead>
					<tbody>
						<xsl:for-each select="set">
							<tr>
								<xsl:attribute name="id">
									<xsl:text>value_</xsl:text>
									<xsl:value-of select="position()"/>
								</xsl:attribute>
								<xsl:if test="../@setImages=1">
									<td>
										<img src="/catalog/ext/{@id}.png" alt="{@name}"/>
									</td>
								</xsl:if>
								<td>
									<xsl:value-of select="@name"/>
								</td>
								<td>
									<xsl:call-template name="actionEdit">
										<xsl:with-param name="link">
											<xsl:text>/adm/catalog/ext/values/edit/</xsl:text>
											<xsl:value-of select="@id"/>
										</xsl:with-param>
									</xsl:call-template>
									<xsl:call-template name="actionUp">
										<xsl:with-param name="link">
											<xsl:text>/adm/catalog/ext/values/up/</xsl:text>
											<xsl:value-of select="@id"/>
										</xsl:with-param>
										<xsl:with-param name="idPrefix">value_</xsl:with-param>
									</xsl:call-template>
									<xsl:call-template name="actionDown">
										<xsl:with-param name="link">
											<xsl:text>/adm/catalog/ext/values/down/</xsl:text>
											<xsl:value-of select="@id"/>
										</xsl:with-param>
										<xsl:with-param name="idPrefix">value_</xsl:with-param>
									</xsl:call-template>
									<xsl:call-template name="actionDelete">
										<xsl:with-param name="link">
											<xsl:text>/adm/catalog/ext/values/delete/</xsl:text>
											<xsl:value-of select="@id"/>
										</xsl:with-param>
									</xsl:call-template>
								</td>
							</tr>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<span class="message">
					<xsl:value-of select="$locale/catalog/adm/ext/values-empty"/>
				</span>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>