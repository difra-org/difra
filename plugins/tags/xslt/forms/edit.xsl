<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="tagsEditForm">
		<div>
			<form class="ajaxer" action="/adm/content/tags/save" id="tagEditForm">
				<h2><xsl:value-of select="$locale/tags/adm/editTagTitle"/></h2>

				<input type="hidden" name="tagId" value="{@id}"/>
				<input type="hidden" name="module" value="{@module}"/>


				<label for="tagName"><xsl:value-of select="$locale/tags/adm/tagName"/></label>
				<input type="text" value="{@tag}" name="tagName" id="tagName"/>

				<input type="submit" value="{$locale/adm/save}"/>
				<input type="button" value="{$locale/tags/adm/createAliase}" onclick="createAliaseForm();"/>
			</form>

			<form class="ajaxer" action="/adm/content/tags/delete" id="tagDeleteForm">
				<input type="hidden" name="tagId" value="{@id}"/>
				<input type="hidden" name="module" value="{@module}"/>
				<input type="submit" value="{$locale/adm/actions/delete}"/>
			</form>

			<form class="ajaxer" action="/adm/content/tags/createaliase/" id="createAliaseForm">
				<h2><xsl:value-of select="$locale/tags/adm/aliaseTitle"/></h2>
				<input type="hidden" name="tagId" value="{@id}"/>
				<input type="hidden" name="module" value="{@module}"/>

				<label for="aliase"><xsl:value-of select="$locale/tags/adm/aliaseLabel"/></label>
				<input type="text" name="aliase" id="aliase"/>

				<input type="submit" value="{$locale/adm/save}"/>
			</form>

			<input type="button" value="{$locale/adm/cancel}" onclick="ajaxer.close()"/>
		</div>
	</xsl:template>
</xsl:stylesheet>