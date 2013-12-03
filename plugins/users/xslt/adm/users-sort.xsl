<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="users-sort">
		<h3>
			<xsl:value-of select="$locale/users/adm/sort/title"/>
		</h3>

		<form class="ajaxer" action="/adm/users/filter/sort">

			<div class="userFilter">
				<select name="sort" class="sortField">
					<option value="registered">
						<xsl:if test="@sortField='registered'">
							<xsl:attribute name="selected">
								<xsl:text>selected</xsl:text>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="$locale/users/adm/sort/registered"/>
					</option>
					<option value="logged">
						<xsl:if test="@sortField='logged'">
							<xsl:attribute name="selected">
								<xsl:text>selected</xsl:text>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="$locale/users/adm/sort/logged"/>
					</option>
					<option value="email">
						<xsl:if test="@sortField='email'">
							<xsl:attribute name="selected">
								<xsl:text>selected</xsl:text>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="$locale/users/adm/sort/email"/>
					</option>
				</select>
				<select name="order" class="orderField">
					<option value="desc">
						<xsl:if test="@sortOrder='desc'">
							<xsl:attribute name="selected">
								<xsl:text>selected</xsl:text>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="$locale/users/adm/sort/desc"/>
					</option>
					<option value="asc">
						<xsl:if test="@sortOrder='asc'">
							<xsl:attribute name="selected">
								<xsl:text>selected</xsl:text>
							</xsl:attribute>
						</xsl:if>
						<xsl:value-of select="$locale/users/adm/sort/asc"/>
					</option>
				</select>
			</div>
			<input type="submit" value="{$locale/users/adm/save}"/>
		</form>
	</xsl:template>
</xsl:stylesheet>

