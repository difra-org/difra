<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="CMSMenuItemAdd">
		<h2>
			<a href="/adm/content/menu">
				<xsl:value-of select="$locale/cms/adm/menu/h2"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/content/menu/view/{@id}">
				<xsl:value-of select="$locale/cms/adm/items/h2"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cms/adm/menuitem/add-item-title"/>
		</h2>
		<h3>
			<xsl:value-of select="$locale/cms/adm/menuitem/options"/>
		</h3>
		<xsl:call-template name="CMSMenuItem"/>
	</xsl:template>

	<xsl:template match="CMSMenuItemEdit">
		<h2>
			<a href="/adm/content/menu">
				<xsl:value-of select="$locale/cms/adm/menu/h2"/>
			</a>
			<xsl:text> → </xsl:text>
			<a href="/adm/content/menu/view/{@id}">
				<xsl:value-of select="$locale/cms/adm/items/h2"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/cms/adm/menuitem/edit-item-title"/>
		</h2>
		<h3>
			<xsl:value-of select="$locale/cms/adm/menuitem/options"/>
		</h3>
		<xsl:call-template name="CMSMenuItem"/>
	</xsl:template>

	<xsl:template name="CMSMenuItem">
		<table class="form">
			<colgroup>
				<col style="width: 250px"/>
				<col/>
			</colgroup>
			<thead>
				<tr>
					<th>
						<xsl:value-of select="$locale/cms/adm/menuitem/type"/>
					</th>
					<td>
						<select name="type" onchange="cms.switchItemForm(this.value)">
							<option value="page">
								<xsl:value-of
									select="$locale/cms/adm/menuitem/type-page"/>
							</option>
							<option value="link">
								<xsl:if test="name()='CMSMenuItemEdit' and not(@page)">
									<xsl:attribute name="selected">selected
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of
									select="$locale/cms/adm/menuitem/type-link"/>
							</option>
						</select>
					</td>
				</tr>
			</thead>
		</table>
		<!-- Добавление/редактирование страницы -->
		<div id="pageForm" class="menuItemForm">
			<xsl:choose>
				<xsl:when test="page">
					<form action="/adm/content/menu/savepage" method="post" class="ajaxer">
						<xsl:choose>
							<xsl:when test="name()='CMSMenuItemAdd'">
								<input type="hidden" name="menu" value="{@id}"/>
							</xsl:when>
							<xsl:when test="name()='CMSMenuItemEdit'">
								<input type="hidden" name="menu" value="{@menu}"/>
								<input type="hidden" name="id" value="{@id}"/>
							</xsl:when>
						</xsl:choose>
						<table class="form">
							<colgroup>
								<col style="width: 250px"/>
								<col/>
							</colgroup>
							<tbody>
								<tr>
									<th>
										<xsl:value-of
											select="$locale/cms/adm/menuitem/type-page"/>
									</th>
									<td>
										<select name="page">
											<xsl:for-each select="page">
												<option value="{@id}">
													<xsl:value-of
														select="@title"/>
												</option>
											</xsl:for-each>
										</select>
									</td>
								</tr>
							</tbody>
						</table>
						<input type="submit" class="large_spacing"
						       value="{$locale/cms/adm/menuitem/submit}"/>
					</form>
				</xsl:when>
				<xsl:otherwise>
					<span class="message">
						<xsl:value-of select="$locale/cms/adm/menuitem/no-pages"/>
					</span>
				</xsl:otherwise>
			</xsl:choose>
		</div>
		<!-- Добавление/редактирование ссылки -->
		<div id="linkForm" style="display:none" class="menuItemForm">
			<form action="/adm/content/menu/savelink" method="post" class="ajaxer">
				<xsl:choose>
					<xsl:when test="name()='CMSMenuItemAdd'">
						<input type="hidden" name="menu" value="{@id}"/>
					</xsl:when>
					<xsl:when test="name()='CMSMenuItemEdit'">
						<input type="hidden" name="menu" value="{@menu}"/>
						<input type="hidden" name="id" value="{@id}"/>
						<xsl:if test="not(@page)">
							<script type="text/javascript">cms.switchItemForm('link');
							</script>
						</xsl:if>
					</xsl:when>
				</xsl:choose>
				<table class="form">
					<colgroup>
						<col style="width: 250px"/>
						<col/>
					</colgroup>
					<tbody>
						<tr>
							<th>
								<xsl:value-of
									select="$locale/cms/adm/menuitem/link-label"/>
							</th>
							<td>
								<input type="text" name="label" class="full-width"
								       value="{@linkLabel}"/>
							</td>
						</tr>
						<tr>
							<th>
								<xsl:value-of
									select="$locale/cms/adm/menuitem/type-link"/>
							</th>
							<td>
								<input type="text" name="link" class="full-width"
								       value="{@link}"/>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="form-buttons">
					<input type="submit" value="{$locale/cms/adm/menuitem/submit}"/>
				</div>
			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>
