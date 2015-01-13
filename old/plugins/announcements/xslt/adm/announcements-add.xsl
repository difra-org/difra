<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsAdd">

		<h2>
			<a href="/adm/announcements/">
				<xsl:value-of select="$locale/announcements/adm/last"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/announcements/adm/add"/>
		</h2>

		<form action="/adm/announcements/save/" class="ajaxer" method="post">

			<h3>
				<xsl:value-of select="$locale/announcements/adm/forms/pic"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 400px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/imagePreview"/>
						<span class="req">*</span>
					</th>
					<td>
						<input type="file" name="eventImage"
						       accept="image/jpeg,image/png,image/gif"/>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/forms/mainParameters"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 400px"/>
					<col/>
				</colgroup>
				<xsl:if test="newGroups/group">
					<tr>
						<th>
							<xsl:value-of select="$locale/announcements/adm/forms/group"/>
						</th>
						<td>
							<select name="group">
								<xsl:for-each select="newGroups/group">
									<option value="{@id}">
										<xsl:if test="@id=1">
											<xsl:attribute name="selected">
												<xsl:text>selected</xsl:text>
											</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="@name"/>
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</xsl:if>

				<xsl:if test="announceCateroty/category">
					<tr>
						<th>
							<xsl:value-of
								select="$locale/announcements/adm/forms/category"/>
						</th>
						<td>
							<select name="category">
								<option value="0">
									<xsl:value-of
										select="$locale/announcements/adm/forms/noSelect"/>
									<xsl:for-each
										select="announceCateroty/category">
										<option value="{@id}">
											<xsl:value-of select="@name"/>
										</option>
									</xsl:for-each>
								</option>
							</select>
						</td>
					</tr>
				</xsl:if>

				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/title"/>
						<span class="req">*</span>
					</th>
					<td>
						<input type="text" name="title" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/priority"/>
					</th>
					<td>
						<input type="hidden" id="priorityValue" name="priorityValue"
						       value="50"/>
						<div id="prioritySlider"/>
						<div id="priorityValueView">50</div>
					</td>
				</tr>

				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/visibility"/>
					</th>
					<td>
						<input type="checkbox" name="visible" value="1" checked="checked"/>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/forms/dates"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 400px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/eventDate"/>
					</th>
					<td>
						<input type="text" name="fromEventDate" id="fromEventDate"
						       placeholder="{$locale/announcements/adm/forms/from}"/>
						<xsl:text> &#8594; </xsl:text>
						<input type="text" name="eventDate" id="eventDate"
						       placeholder="{$locale/announcements/adm/forms/to}"/>
						<span class="req">*</span>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/beginDate"/>
						<span class="req">*</span>
					</th>
					<td>
						<input type="text" name="beginDate" id="beginDate" disabled="disabled"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/endDate"/>
						<span class="req">*</span>
					</th>
					<td>
						<input type="text" name="endDate" id="endDate" disabled="disabled"/>
					</td>
				</tr>
			</table>

			<xsl:if test="locations/item">
				<colgroup>
					<col style="width: 400px"/>
					<col/>
				</colgroup>
				<h3>
					<xsl:value-of select="$locale/announcements/adm/forms/location"/>
				</h3>

				<table class="form">
					<colgroup>
						<col style="width: 400px"/>
						<col/>
					</colgroup>
					<tr>
						<th>
							<xsl:value-of
								select="$locale/announcements/adm/forms/chooseLocation"/>
						</th>
						<td>
							<select name="location">
								<option value="0">
									<xsl:value-of
										select="$locale/announcements/adm/forms/noSelect"/>
								</option>
								<xsl:for-each select="locations/item">
									<option value="{@id}">
										<xsl:value-of select="@name"/>
									</option>
								</xsl:for-each>
							</select>
						</td>
					</tr>
				</table>
			</xsl:if>

			<xsl:if test="additionalsFields/item">
				<h3>
					<xsl:value-of select="$locale/announcements/adm/additionals/title"/>
				</h3>

				<table class="form">
					<colgroup>
						<col style="width: 400px"/>
						<col/>
					</colgroup>
					<xsl:for-each select="additionalsFields/item">
						<tr>
							<th>
								<xsl:value-of select="@name"/>
							</th>
							<td>
								<input type="text" name="additionalField[{@id}]" class="full-width"/>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:if>

			<h3>
				<xsl:value-of
					select="$locale/announcements/adm/forms/shortDescription"/>
			</h3>

			<textarea name="shortDescription" cols="" rows="10"/>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/forms/description"/>
				<span class="req">*</span>
			</h3>

			<textarea name="description" editor="Full" cols="" rows=""/>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/schedules/title"/>
			</h3>
			<table class="form schedules">
				<colgroup>
					<col style="width: 400px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of
							select="$locale/announcements/adm/schedules/scheduleName"/>
					</th>
					<td>
						<input type="text" name="scheduleName" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/schedules/add"/>
					</th>
					<td>
						<a href="#" class="action add"
						   onclick="announcementsUI.addSchedule();"/>
					</td>
				</tr>
			</table>

			<div id="schedulesFields">

			</div>

			<input type="submit" value="{$locale/announcements/adm/forms/addEvent}"/>
		</form>

		<div class="no-display" id="schedulesFieldAdd">
			<label class="small gray">
				<xsl:value-of select="$locale/announcements/adm/schedules/sessionLabel"/>
			</label>
			<input type="text" class="sn"/>

			<label class="small gray">
				<xsl:value-of select="$locale/announcements/adm/schedules/sessionDop"/>
			</label>
			<input type="text" class="sv"/>
			<a href="#" class="action delete" onclick="announcementsUI.deleteSchedule( this );"/>
		</div>

	</xsl:template>
</xsl:stylesheet>