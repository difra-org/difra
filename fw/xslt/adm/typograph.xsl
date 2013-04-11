<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="typograph">
		<h2>
			<xsl:value-of select="$locale/adm/typograph/settings"/>
		</h2>

		<h3>
			<xsl:value-of select="$locale/adm/typograph/mainSettings"/>
		</h3>

		<form class="ajaxer" action="/adm/development/typograph/save/" method="post">
			<table class="form">
				<tr>
					<th>
						<label for="spaceAfterShortWord">
							<xsl:value-of select="$locale/adm/typograph/spaceAfterShortWord"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="spaceAfterShortWord" id="spaceAfterShortWord">
							<xsl:if test="@spaceAfterShortWord=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/typograph/lengthShortWord"/>
					</th>
					<td>
						<input type="number" name="lengthShortWord" value="{@lengthShortWord}"/>
					</td>
				</tr>
				<tr>
					<th>
						<label for="spaceBeforeLastWord">
							<xsl:value-of select="$locale/adm/typograph/spaceBeforeLastWord"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="spaceBeforeLastWord" id="spaceBeforeLastWord">
							<xsl:if test="@spaceBeforeLastWord=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/typograph/lengthLastWord"/>
					</th>
					<td>
						<input type="number" name="lengthLastWord" id="lengthLastWord" value="{@lengthLastWord}" />
					</td>
				</tr>
				<tr>
					<th>
						<label for="spaceAfterNum">
							<xsl:value-of select="$locale/adm/typograph/spaceAfterNum"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="spaceAfterNum" id="spaceAfterNum">
							<xsl:if test="@spaceAfterNum=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="spaceBeforeParticles">
							<xsl:value-of select="$locale/adm/typograph/spaceBeforeParticles"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="spaceBeforeParticles" id="spaceBeforeParticles">
							<xsl:if test="@spaceBeforeParticles=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="delRepeatSpace">
							<xsl:value-of select="$locale/adm/typograph/delRepeatSpace"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="delRepeatSpace" id="delRepeatSpace">
							<xsl:if test="@delRepeatSpace=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="delSpaceBeforePunctuation">
							<xsl:value-of select="$locale/adm/typograph/delSpaceBeforePunctuation"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="delSpaceBeforePunctuation" id="delSpaceBeforePunctuation">
							<xsl:if test="@delSpaceBeforePunctuation=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="delSpaceBeforeProcent">
							<xsl:value-of select="$locale/adm/typograph/delSpaceBeforeProcent"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="delSpaceBeforeProcent" id="delSpaceBeforeProcent">
							<xsl:if test="@delSpaceBeforeProcent=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/adm/typograph/behavior"/>
			</h3>

			<table class="form">
				<tr>
					<th>
						<label for="doReplaceBefore">
							<xsl:value-of select="$locale/adm/typograph/doReplaceBefore"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="doReplaceBefore" id="doReplaceBefore">
							<xsl:if test="@doReplaceBefore=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="doReplaceAfter">
							<xsl:value-of select="$locale/adm/typograph/doReplaceAfter"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="doReplaceAfter" id="doReplaceAfter">
							<xsl:if test="@doReplaceAfter=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
				<tr>
					<th>
						<label for="doMacros">
							<xsl:value-of select="$locale/adm/typograph/doMacros"/>
						</label>
					</th>
					<td>
						<input type="checkbox" name="doMacros" id="doMacros">
							<xsl:if test="@doMacros=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<input type="submit" value="{$locale/adm/save}"/>
		</form>

	</xsl:template>
</xsl:stylesheet>