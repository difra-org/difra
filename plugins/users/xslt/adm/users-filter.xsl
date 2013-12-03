<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="users-filter">
		<h3>
			<xsl:value-of select="$locale/users/adm/filter/title"/>
		</h3>

		<form class="ajaxer" action="/adm/users/filter/save">

			<div class="userFilter">
				<label>
					<input type="checkbox" name="active">
						<xsl:if test="@active and @active=1 or not(@active)">
							<xsl:attribute name="checked">
								<xsl:text>checked</xsl:text>
							</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:value-of select="$locale/users/adm/filter/noActive"/>
				</label>

				<label>
					<input type="checkbox" name="ban">
						<xsl:if test="@ban and @ban=1 or not(@ban)">
							<xsl:attribute name="checked">
								<xsl:text>checked</xsl:text>
							</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:value-of select="$locale/users/adm/filter/banned"/>
				</label>

				<label>
					<input type="checkbox" name="moderator">
						<xsl:if test="@moderator and @moderator=1 or not(@moderator)">
							<xsl:attribute name="checked">
								<xsl:text>checked</xsl:text>
							</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:value-of select="$locale/users/adm/filter/moderator"/>
				</label>

				<label>
					<input type="checkbox" name="noLogin">
						<xsl:if test="@noLogin and @noLogin=1 or not(@noLogin)">
							<xsl:attribute name="checked">
								<xsl:text>checked</xsl:text>
							</xsl:attribute>
						</xsl:if>
					</input>
					<xsl:value-of select="$locale/users/adm/filter/noLogin"/>
				</label>
			</div>
			<input type="submit" value="{$locale/users/adm/save}"/>
		</form>
	</xsl:template>
</xsl:stylesheet>

