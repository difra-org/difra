<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template name="video-in">

		<xsl:choose>
			<xsl:when test="videoIn/error">

				<xsl:variable name="errorType" select="videoIn/error/@type"/>
				<div class="errorsDiv">
					<xsl:value-of select="$locale/videoManager/adm/errors/*[name()=$errorType]"/>
				</div>
			</xsl:when>
			<xsl:otherwise>

				<table width="98%">
					<tr>
						<th></th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/file"/>
						</th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/size"/>
						</th>
						<th width="40%">
							<xsl:value-of select="$locale/videoManager/adm/action"/>
						</th>
					</tr>

					<xsl:for-each select="videoIn/file">
						<xsl:if test="not(@name=/root/video-manager/videoOut/item/@original_file)">
							<tr>
								<td valign="top"><xsl:value-of select="position()"/>.
								</td>
								<td valign="top">
									<xsl:choose>
										<xsl:when test="@trash and @trash=1">
											<span class="gray">
												<xsl:value-of select="@name"/>
											</span>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="@name"/>
										</xsl:otherwise>
									</xsl:choose>
								</td>
								<td valign="top"><xsl:value-of select="@size"/>mb
								</td>
								<td valign="top">
									<div id="videoAction-{position()}">
										<a href="#" class="action" onclick="addVideo( {position()} );">
											<xsl:value-of select="$locale/videoManager/adm/addVideo"/>
										</a>
										<a href="/adm/videomanager/delete/name/{@name}"
										   class="action delete ajaxer">delete
										</a>
									</div>

									<div id="videoAdd-{position()}" class="videoAdd">
										<form class="ajaxer" action="/adm/videomanager/addvideo/">
											<div class="vidTitle">
												<xsl:value-of select="$locale/videoManager/adm/addTitle"/>
											</div>
											<input name="filename" type="hidden" value="{@name}"/>
											<label for="name">
												<xsl:value-of select="$locale/videoManager/adm/videoName"/>
											</label>
											<input name="name" type="text" id="name"/>

											<label for="poster">
												<xsl:value-of select="$locale/videoManager/adm/poster"/>
											</label>
											<input name="poster" type="file" id="poster"/>
											<br/>
											<input type="submit" value="{$locale/videoManager/adm/addVideo}"/>
											<input type="button" value="{$locale/videoManager/adm/cancel}"
											       onclick="closeAdd( {position()} );"/>
										</form>
										<br/>
									</div>
								</td>
							</tr>
						</xsl:if>
					</xsl:for-each>
				</table>
			</xsl:otherwise>
		</xsl:choose>

	</xsl:template>
</xsl:stylesheet>