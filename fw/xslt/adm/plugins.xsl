<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="/root/plugins">
		<h2>Plugins</h2>
		<table>
			<tr>
				<th/>
				<th>Plugin</th>
				<th>Requires</th>
				<th>Required by</th>
			</tr>
			<xsl:for-each select="*">
				<xsl:sort select="name()"/>
				<xsl:variable name="name" select="name()"/>
				<tr>
					<td>
						<input type="checkbox" name="plugins[{$name}][enable]">
							<xsl:choose>
								<xsl:when test="@missingReq=1 or @disabled=1">
									<xsl:attribute name="disabled">
										<xsl:text>disabled</xsl:text>
									</xsl:attribute>
								</xsl:when>
								<xsl:when test="@enabled=1 and not(@disabled=1)">
									<xsl:attribute name="checked">
										<xsl:text>checked</xsl:text>
									</xsl:attribute>
								</xsl:when>
							</xsl:choose>
						</input>
					</td>
					<td>
						<xsl:value-of select="$name"/>
					</td>
					<td>
						<xsl:for-each select="require/*">
							<xsl:if test="position()>1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="name()"/>
						</xsl:for-each>
					</td>
					<td>
						<xsl:for-each select="required/*">
							<xsl:if test="position()>1">
								<xsl:text>, </xsl:text>
							</xsl:if>
							<xsl:value-of select="name()"/>
						</xsl:for-each>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>