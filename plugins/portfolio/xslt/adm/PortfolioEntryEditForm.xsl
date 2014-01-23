<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template name="PortfolioEntryEditForm">
		<form action="/adm/content/portfolio/save" method="post" class="ajaxer">

			<xsl:if test="entry/images/PortfolioImagesList/PortfolioImages">
				<h3>
					<xsl:value-of select="$locale/portfolio/entry/pics"/>
				</h3>

				<xsl:for-each select="entry/images/PortfolioImagesList/PortfolioImages">
					<div>
						<xsl:attribute name="class">
							<xsl:text>portfolio-image</xsl:text>
							<xsl:if test="position()=1">
								<xsl:text> main</xsl:text>
							</xsl:if>
						</xsl:attribute>
						<img src="/portimages/{@id}-medium.png" />

						<div class="controls">
							<xsl:call-template name="actionLeft">
								<xsl:with-param name="link">
									<xsl:text>/adm/content/portfolio/imageup</xsl:text>
									<xsl:value-of select="../@id"/>
									<xsl:text>/</xsl:text>
									<xsl:value-of select="@id"/>
								</xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="actionRight">
								<xsl:with-param name="link">
									<xsl:text>/adm/content/portfolio/imagedown</xsl:text>
									<xsl:value-of select="../@id"/>
									<xsl:text>/</xsl:text>
									<xsl:value-of select="@id"/>
								</xsl:with-param>
							</xsl:call-template>
							<xsl:call-template name="actionDelete">
								<xsl:with-param name="link">
									<xsl:text>/adm/content/portfolio/deleteimage</xsl:text>
									<xsl:value-of select="../@id"/>
									<xsl:text>/</xsl:text>
									<xsl:value-of select="@id"/>
								</xsl:with-param>
							</xsl:call-template>
						</div>
					</div>

				</xsl:for-each>
			</xsl:if>

			<h3>
				<xsl:value-of select="$locale/portfolio/entry/loadPics"/>
			</h3>
			<input type="file" name="image[]" class="ajaxer" multiple="multiple" />

			<h3>
				<xsl:value-of select="$locale/portfolio/entry/mainParams"/>
			</h3>

			<table>
				<colgroup>
					<col style="width:250px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<label for="name">
							<xsl:value-of select="$locale/portfolio/entry/name"/>
						</label>
					</th>
					<td>
						<div class="container">
							<input type="text" name="name" id="name" class="full-width" value="{entry/@name}"/>
							<div class="status"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="release">
							<xsl:value-of select="$locale/portfolio/entry/release"/>
						</label>
					</th>
					<td>
						<input type="date" name="release" id="release" class="full-width" value="{entry/@release}"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="link">
							<xsl:value-of select="$locale/portfolio/entry/link"/>
						</label>
					</th>
					<td>
						<input type="url" name="link" id="link" class="full-width" value="{entry/@link}"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="link_caption">
							<xsl:value-of select="$locale/portfolio/entry/link_caption"/>
						</label>
					</th>
					<td>
						<input type="text" name="link_caption" id="link_caption" class="full-width" value="{entry/@link_caption}"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="software" id="software">
							<xsl:value-of select="$locale/portfolio/entry/software"/>
						</label>
					</th>
					<td>
						<input type="text" name="software" class="full-width" value="{entry/@software}"/>
					</td>
				</tr>
			</table>

			<h3>
				<label for="authors">
					<xsl:value-of select="$locale/portfolio/entry/authors"/>
				</label>
			</h3>
			<table class="portfolio-roles">
				<colgroup>
					<col style="width:250px"/>
					<col/>
				</colgroup>
				<thead>
					<tr>
						<th>
							<xsl:value-of select="$locale/portfolio/entry/role"/>
						</th>
						<th>
							<xsl:value-of select="$locale/portfolio/entry/contributors"/>
						</th>
					</tr>
				</thead>

				<xsl:if test="entry/role">

					<xsl:for-each select="entry/role">
						<xsl:variable name="currentRole" select="position()"/>
						<tr>
							<td>
								<a class="action delete" onclick="$(this).parent().parent().remove()" href="#"></a>
								<xsl:text>&#160;&#160;&#160;</xsl:text>
								<xsl:value-of select="@name"/>
								<input class="portfolio-role" type="hidden" ts="{position()}" value="{@name}"
								       name="roles[{position()}][role]"></input>
							</td>
							<td class="add-person">
								<xsl:if test="current()/contibutor">
									<xsl:for-each select="current()/contibutor">
										<div class="portfolio-person">
											<xsl:value-of select="@name"/>
											<input type="hidden" value="{@name}" name="roles[{$currentRole}][]"/>
											<a class="action delete"
											   onclick="$(this).parent().remove();" href="#"></a>
										</div>
									</xsl:for-each>
								</xsl:if>
								<a class="action add ajaxer widgets-directory last" href="/adm/content/portfolio/persons"></a>
							</td>
						</tr>
					</xsl:for-each>
				</xsl:if>

				<tbody>
					<tr id="add-role">
						<td>
							<a href="/adm/content/portfolio/roles" class="action add ajaxer widgets-directory"/>
						</td>
						<td>
						</td>
					</tr>
				</tbody>
			</table>
			<h3>
				<label for="description">
					<xsl:value-of select="$locale/portfolio/entry/description"/>
				</label>
			</h3>
			<textarea name="description" editor="full" id="description">
				<xsl:value-of select="entry/@description" disable-output-escaping="yes"/>
			</textarea>

			<xsl:if test="@edit=1">
				<input type="hidden" name="id" value="{entry/@id}"/>
			</xsl:if>

			<div class="form-buttons">
				<input type="submit" value="{$locale/portfolio/entry/form-submit}"/>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>