<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:template name="PortfolioEntryEditForm">
		<form action="/adm/content/portfolio/save" method="post" class="ajaxer">
			<h3>

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
						<input type="text" name="name" id="name" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="release">
							<xsl:value-of select="$locale/portfolio/entry/release"/>
						</label>
					</th>
					<td>
						<input type="date" name="release" id="release" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="link">
							<xsl:value-of select="$locale/portfolio/entry/link"/>
						</label>
					</th>
					<td>
						<input type="url" name="link" id="link" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="software" id="software">
							<xsl:value-of select="$locale/portfolio/entry/software"/>
						</label>
					</th>
					<td>
						<input type="text" name="software" class="full-width"/>
					</td>
				</tr>
			</table>
			<h3>
				<label for="authors">
					<xsl:value-of select="$locale/portfolio/entry/authors"/>
				</label>
			</h3>
			<table>
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

			</textarea>
			<div class="form-buttons">
				<input type="submit" value="{$locale/portfolio/entry/form-submit}"/>
			</div>
		</form>
	</xsl:template>
</xsl:stylesheet>