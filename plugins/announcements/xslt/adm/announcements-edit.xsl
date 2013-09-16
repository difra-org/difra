<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsEdit">

		<h2>
			<a href="/adm/announcements/">
				<xsl:value-of select="$locale/announcements/adm/announcements"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/announcements/adm/edit"/>
		</h2>

		<form action="/adm/announcements/update/" class="ajaxer" method="post">

			<xsl:variable name="aId" select="event/id"/>
			<xsl:variable name="cId" select="event/category"/>

			<input type="hidden" name="id" value="{event/id}"/>

			<h3><xsl:value-of select="$locale/announcements/adm/forms/pic"/></h3>

			<table class="form announcePreview">
				<colgroup>
					<col style="width: 250px;"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/imagePreview"/>
						<br/>
						<img src="/announcements/{event/id}.png" alt=""/>
					</th>
					<td>
						<input type="file" name="eventImage" accept="image/jpeg,image/png,image/gif" class="full-width"/>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/forms/mainParameters"/>
			</h3>

			<table class="form">
				<colgroup>
					<col style="width: 250px"/>
					<col/>
				</colgroup>
				<xsl:if test="newGroups/group">

					<tr>
						<th>
							<xsl:value-of select="$locale/announcements/adm/forms/group"/>
						</th>
						<td>
							<select name="group" class="full-width">
								<xsl:for-each select="newGroups/group">
									<option value="{@id}">
										<xsl:if test="@id=/root/announcementsEdit/event/group">
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
					<colgroup>
						<col style="width: 250px;"/>
						<col/>
					</colgroup>
					<tr>
						<th>
							<xsl:value-of select="$locale/announcements/adm/forms/category"/>
						</th>
						<td>
							<select name="category" class="full-width">
								<option value="0">
									<xsl:value-of select="$locale/announcements/adm/forms/noSelect"/>
									<xsl:for-each select="announceCateroty/category">
										<option value="{@id}">
											<xsl:if test="@id=$cId">
												<xsl:attribute name="selected">
													<xsl:text>selected</xsl:text>
												</xsl:attribute>
											</xsl:if>
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
					</th>
					<td>
						<input type="text" name="title" value="{event/title}" class="full-width" />
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/priority"/>
					</th>
					<td>
						<input type="hidden" id="priorityValue" name="priorityValue" value="{event/priority}"/>
						<div id="prioritySlider"/>
						<div id="priorityValueView">
							<xsl:value-of select="event/priority"/>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/visibility"/>
					</th>
					<td>
						<input type="checkbox" name="visible" value="1">
							<xsl:if test="event/visible=1">
								<xsl:attribute name="checked">
									<xsl:text>checked</xsl:text>
								</xsl:attribute>
							</xsl:if>
						</input>
					</td>
				</tr>
			</table>

			<h3><xsl:value-of select="$locale/announcements/adm/forms/dates"/></h3>

			<table class="form">
				<colgroup>
					<col style="width: 350px"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/eventDate"/>
					</th>
					<td>
						<input type="text" name="fromEventDate" id="fromEventDate"
						       placeholder="{$locale/announcements/adm/forms/from}">
							<xsl:if test="not(event/fromEventDate=event/eventDate)">
								<xsl:attribute name="value">
									<xsl:value-of select="event/fromEventDate"/>
								</xsl:attribute>
							</xsl:if>
						</input>
						<xsl:text> &#8594; </xsl:text>
						<input type="text" name="eventDate" id="eventDate"
						       placeholder="{$locale/announcements/adm/forms/to}" value="{event/eventDate}"/>
						<span class="req">*</span>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/beginDate"/>
					</th>
					<td>
						<input type="text" name="beginDate" id="beginDate" disabled="disabled" value="{event/beginDate}"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/endDate"/>
					</th>
					<td>
						<input type="text" name="endDate" id="endDate" disabled="disabled" value="{event/endDate}"/>
					</td>
				</tr>
			</table>

			<xsl:if test="locations/item">
				<h3>
					<xsl:value-of select="$locale/announcements/adm/forms/location"/>
				</h3>

				<table class="form">
					<colgroup>
						<col style="width: 250px;"/>
						<col/>
					</colgroup>
					<tr>
						<th>
							<xsl:value-of select="$locale/announcements/adm/forms/chooseLocation"/>
						</th>
						<td>
							<select name="location">
								<option value="0">
									<xsl:value-of select="$locale/announcements/adm/forms/noSelect"/>
								</option>
								<xsl:for-each select="locations/item">
									<option value="{@id}">
										<xsl:if test="/root/announcementsEdit/event/location=@id">
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
				</table>
			</xsl:if>

			<xsl:if test="additionalsFields/item">
				<h3><xsl:value-of select="$locale/announcements/adm/additionals/title"/></h3>

				<table class="form">
					<colgroup>
						<col style="width: 250px;"/>
						<col/>
					</colgroup>
					<xsl:for-each select="additionalsFields/item">
						<xsl:variable name="fId" select="@id"/>
						<tr>
							<th>
								<xsl:value-of select="@name"/>
							</th>
							<td>
								<input type="text" name="additionalField[{@id}]" class="full-width"
								       value="{/root/announcementsEdit/event/additionals/field[@id=$fId]/@value}"/>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:if>

			<h3><xsl:value-of select="$locale/announcements/adm/forms/eventDescription"/></h3>

			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/shortDescription"/>
					</th>
					<td>
						<textarea name="shortDescription" rows="10" cols="">
							<xsl:value-of select="event/shortDescription" disable-output-escaping="yes"/>
						</textarea>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/forms/description"/>
					</th>
					<td>
						<textarea name="description" editor="Full" rows="" cols="">
							<xsl:value-of select="event/description" disable-output-escaping="yes"/>
						</textarea>
					</td>
				</tr>
			</table>

			<h3>
				<xsl:value-of select="$locale/announcements/adm/schedules/title"/>
			</h3>
			<table class="form schedules">
				<colgroup>
					<col style="width: 250px;"/>
					<col/>
				</colgroup>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/schedules/scheduleName"/>
					</th>
					<td>
						<input type="text" name="scheduleName" value="{event/schedules/@title}" class="full-width"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/announcements/adm/schedules/add"/>
					</th>
					<td>
						<a href="#" class="action add" onclick="announcementsUI.addSchedule();"/>
					</td>
				</tr>
			</table>

			<div id="schedulesFields">

				<xsl:if test="event/schedules/item">
					<xsl:for-each select="event/schedules/item">
						<div>
							<label class="small gray">
								<xsl:value-of select="$locale/announcements/adm/schedules/sessionLabel"/>
							</label>
							<input type="text" class="sn" name="scheduleField[{position()}]" value="{@name}"/>
							<label class="small gray">
								<xsl:value-of select="$locale/announcements/adm/schedules/sessionDop"/>
							</label>
							<input type="text" class="sv" name="scheduleValue[{position()}]" value="{@value}"/>
							<a href="#" class="action delete" onclick="announcementsUI.deleteSchedule( this );"/>
						</div>
					</xsl:for-each>
					<script type="text/javascript">
						announcementsUI.setScheduleCount(<xsl:value-of select="count( * )+1"/>);
					</script>
				</xsl:if>
			</div>
			<br/>
			<input type="submit" value="{$locale/announcements/adm/forms/saveEvent}"/>
			<br/>
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