<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="adm_tags">

		<h2>
			<xsl:value-of select="$locale/tags/adm/tagsTitle"/>
		</h2>

		<h3>
			<xsl:value-of select="$locale/tags/adm/tags"/>
		</h3>

		<table class="sortedTags">
			<tr>
				<xsl:call-template name="sortedTags">
					<xsl:with-param name="nodelist" select="/root/adm_tags/tags/*"/>
					<xsl:with-param name="columns" select="4"/>
				</xsl:call-template>
			</tr>
		</table>

		<h3><xsl:value-of select="$locale/tags/adm/aliases"/></h3>
		
		<table class="sortedTags">
			<tr>
				<td>
					<xsl:for-each select="/root/aliases/alias[position() mod 2 &gt; 0]">
						<span class="aliasTitle">
							<xsl:value-of select="@name"/>
						</span>
						<ul>
							<xsl:for-each select="current()/tag">
								<li class="aliasTag">
									<xsl:value-of select="@name"/>
									<a href="/adm/tags/deletealias/{@id}/" class="aliasDeleteLink ajaxer">
										<xsl:value-of select="$locale/adm/actions/delete"/>
									</a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:for-each>
				</td>
				<td>
					<xsl:for-each select="/root/aliases/alias[position() mod 2 = 0]">
						<span class="aliasTitle">
							<xsl:value-of select="@name"/>
						</span>
						<ul>
							<xsl:for-each select="current()/tag">
								<li class="aliasTag">
									<xsl:value-of select="@name"/>
									<a href="/adm/tags/deletealias/{@id}/" class="aliasDeleteLink ajaxer">
										<xsl:value-of select="$locale/adm/actions/delete"/>
									</a>
								</li>
							</xsl:for-each>
						</ul>
					</xsl:for-each>
				</td>
			</tr>
		</table>

	</xsl:template>
</xsl:stylesheet>