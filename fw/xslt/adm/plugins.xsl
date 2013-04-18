<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="plugins">
		<h2>Plugins</h2>
		<xsl:choose>
			<xsl:when test="plugins/*">
				<table>
					<tr>
						<th/>
						<th><xsl:value-of select="$locale/adm/plugins/name"/></th>
						<th><xsl:value-of select="$locale/adm/plugins/description"/></th>
						<th><xsl:value-of select="$locale/adm/plugins/version"/></th>
						<th><xsl:value-of select="$locale/adm/plugins/requires"/></th>
					</tr>
					<xsl:for-each select="plugins/*">
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
								<xsl:value-of select="@description"/>
							</td>
							<td>
								<xsl:choose>
									<xsl:when test="@old">
										<span class="error">
											<xsl:value-of select="@version"/>
										</span>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select="@version"/>
									</xsl:otherwise>
								</xsl:choose>
							</td>
							<td>
								<xsl:for-each select="require/*">
									<xsl:sort select="name()"/>
									<xsl:variable name="reqName" select="name()"/>
									<xsl:if test="position()>1">
										<xsl:text>, </xsl:text>
									</xsl:if>
									<xsl:choose>
										<xsl:when test="../../missingReq/*[name()=$reqName]">
											<xsl:variable name="url" select="../../../../provisions/*[name()=$reqName]/@url"/>
											<xsl:choose>
												<xsl:when test="$url">
													<a href="{$url}" class="dashed error">
														<xsl:value-of select="name()"/>
													</a>
												</xsl:when>
												<xsl:otherwise>
													<span class="error">
														<xsl:value-of select="name()"/>
													</span>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="name()"/>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:for-each>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$locale/adm/plugins/no-plugins"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>