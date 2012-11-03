<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:key name="index" match="/root/adm_tags/tags/*" use="substring( @tag, 1, 1 )"/>

	<xsl:template name="sortedTags">
		<xsl:param name="nodelist" select="."/>
		<xsl:param name="columns" select="1"/>
		<xsl:param name="column-start" select="0"/>

		<xsl:variable name="blocks" select="$nodelist[generate-id() = generate-id( key( 'index', substring( @tag, 1, 1 ) ) )]"/>
		<xsl:variable name="total" select="count($blocks)"/>
		<xsl:variable name="percolumn" select="ceiling( $total div $columns )"/>
		<td valign="top">
			<xsl:for-each select="$blocks">
				<xsl:sort select="@tag"/>
				<xsl:variable name="ind" select="substring( @tag, 1, 1 )"/>
				<xsl:if test="position() &gt; $column-start and position() &lt;= $column-start + $percolumn">
					<div class="first">
						<xsl:value-of select="$ind"/>
					</div>
					<ul>
						<xsl:for-each select="$nodelist">
							<xsl:sort select="@tag"/>
							<xsl:if test="substring(@tag,1,1)=$ind">
								<li>
									<a href="/adm/tags/edit/{@module}/{@id}" class="ajaxer">
										<xsl:value-of select="@tag"/>
									</a>
								</li>
							</xsl:if>
						</xsl:for-each>
					</ul>
				</xsl:if>
			</xsl:for-each>
		</td>
		<xsl:if test="$column-start + ( $total div $columns ) &lt; $total">
			<xsl:call-template name="sortedTags">
				<xsl:with-param name="nodelist" select="$nodelist"/>
				<xsl:with-param name="columns" select="$columns"/>
				<xsl:with-param name="column-start" select="$column-start + $percolumn"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

</xsl:stylesheet>