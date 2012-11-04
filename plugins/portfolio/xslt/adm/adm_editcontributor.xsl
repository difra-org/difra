<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="portfolio-editContributor">
		<h2>
			<a href="/adm/portfolio/contributors">
				<xsl:value-of select="$locale/contributors/title"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/contributors/edit-title"/>
		</h2>

		<form action="/adm/portfolio/savecontributor/" name="savecontributor" id="savecontributor"
              enctype="multipart/form-data" method="post" class="ajaxer">
			<input type="hidden" name="user" value="{/root/contributor/@id}"/>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/contributors/labels/name"/>
					</th>
					<td>
						<input name="name" id="name" type="text" value="{/root/contributor/@name}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/contributors/labels/linkText"/>
					</th>
					<td>
						<input name="linktext" id="linkText" type="text" value="{/root/contributor/@linktext}"/>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/contributors/labels/defaultRole"/>
					</th>
					<td>
						<input name="role" id="role" type="text" value="{/root/contributor/@role}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/contributors/labels/goArchive"/>
					</th>
					<td>
						<input name="archive" id="archive" type="checkbox" value="1">
							<xsl:if test="/root/contributor/@archive=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<input type="submit" id="sendContributor" value="{$locale/contributors/labels/save}" />
		</form>
	</xsl:template>
</xsl:stylesheet>
