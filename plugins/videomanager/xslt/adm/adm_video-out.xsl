<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template name="video-out">

		<xsl:choose>
			<xsl:when test="videoOut/error">

				<xsl:variable name="errorType" select="videoOut/error/@type"/>
				<div class="errorsDiv">
					<xsl:value-of select="$locale/videoManager/adm/errors/*[name()=$errorType]"/>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<table width="98%">
					<tr>
						<th></th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/video"/>
						</th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/status"/>
						</th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/preview"/>
						</th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/poster"/>
						</th>
						<th>
							<xsl:value-of select="$locale/videoManager/adm/action"/>
						</th>
					</tr>

					<xsl:for-each select="videoOut/item">
						<tr>
							<td>
								<xsl:value-of select="position()"/>
							</td>
							<td>
								<form action="/adm/videomanager/changename/" class="ajaxer">
									<input type="hidden" name="videoId" value="{@id}"/>

									<div class="vidTitle" id="videoNameDiv-{@id}">
										<xsl:value-of select="@name"/>
									</div>
									<div class="videoAdd" id="videoNameDivEdit-{@id}">
										<input type="text" value="{@name}" name="name"/>
									</div>

									<span class="gray">
										<xsl:value-of select="$locale/videoManager/adm/hash"/>
										<xsl:value-of select="@video"/>
										<br/>
										<xsl:value-of select="$locale/videoManager/adm/date"/>
										<xsl:value-of select="@date"/>
										<br/>
										<xsl:value-of select="$locale/videoManager/adm/lenght"/>
										<xsl:choose>
											<xsl:when test="@lenght and not(@lenght='')">
												<xsl:value-of select="@lenght"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of select="$locale/videoManager/adm/notDefined"/>
											</xsl:otherwise>
										</xsl:choose>
									</span>
									
									<div class="videoAdd" id="videoNameSubmit-{@id}">
										<input type="submit" value="{$locale/videoManager/adm/save}"/>
										<input type="button" value="{$locale/videoManager/adm/cancel}"
										       onclick="videoEditClose( {@id} );"/>
									</div>
									
								</form>
							</td>
							<td>
								<xsl:variable name="status" select="@status"/>
								<xsl:value-of select="$locale/videoManager/adm/states/*[name()=concat('state_', $status)]"/>
							</td>
							<td>
								<xsl:value-of select="@thumbs"/>
							</td>
							<td>
								<div class="posterDiv" id="posterDiv-{@id}">
									<xsl:choose>
										<xsl:when test="@hasPoster=1">
											<a href="{/root/video-preview/@posters}{@video}_720_0.png">
												<img src="{/root/video-preview/@posters}{@video}_thumb.png"/>
											</a>
											<br/>
											<a href="#" onclick="addPoster( {@id} );">
												<xsl:value-of select="$locale/videoManager/adm/change"/>
											</a>
										</xsl:when>
										<xsl:otherwise>
											<a href="#" onclick="addPoster( {@id} );">
												<xsl:value-of select="$locale/videoManager/adm/add"/>
											</a>
										</xsl:otherwise>
									</xsl:choose>
								</div>

								<div id="posterChangeDiv-{@id}" class="posterChangeDiv">
									<form action="/adm/videomanager/changeposter/" class="ajaxer">
										<label for="poster">
											<xsl:value-of select="$locale/videoManager/adm/poster"/>
										</label>
										<input type="hidden" name="videoHash" value="{@video}"/>
										<input type="file" name="poster"/>
										<br/>

										<input type="submit" value="Загрузить постер"/>
										<input type="button" value="Отмена" onclick="closeAddPoster( {@id} );"/>
										<br/>
										<br/>
									</form>
								</div>
							</td>
							<td>
								<xsl:if test="@status=0">
									<a href="/adm/videomanager/encode/{@id}/" class="action ajaxer">
										<xsl:value-of select="$locale/videoManager/adm/toEncode"/>
									</a>
								</xsl:if>

								<a href="#" class="action edit" onclick="editVideoName( {@id} );">edit</a>

								<xsl:if test="not(@status=2)">
									<a href="/adm/videomanager/deleteadded/{@id}/" class="action delete ajaxer">
										delete
									</a>
								</xsl:if>
							</td>
						</tr>
					</xsl:for-each>

				</table>
			</xsl:otherwise>
		</xsl:choose>

	</xsl:template>
</xsl:stylesheet>