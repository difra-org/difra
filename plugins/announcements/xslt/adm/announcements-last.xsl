<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="announcementsLast">

		<h2>
			<xsl:value-of select="$locale/announcements/adm/last"/>
		</h2>

		<a href="/adm/announcements/add/" class="action add"></a>

		<h3>
			<xsl:value-of select="$locale/announcements/adm/nextEvents"/>
		</h3>
		<table>
			<colgroup>
				<col/>
				<col/>
				<col style="width: 160px"/>
				<col style="width: 200px"/>
				<col/>
				<col/>
				<col style="width: 115px"/>
			</colgroup>
			<tr>
				<th></th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/event_desc"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/event_dates"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/users_groups"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/category/category"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/priority"/>
				</th>
				<th></th>
			</tr>
			<xsl:for-each select="announcements/event[not(status='past')]">
				<tr>
					<td>
						<xsl:value-of select="position()"/>
					</td>
					<td>

						<xsl:variable name="statusText" select="status"/>

						<strong>
							<xsl:value-of select="title"/>
						</strong>
						<div class="eventDate">
							<xsl:choose>
								<xsl:when test="status='inFuture'">
									<xsl:choose>
										<xsl:when test="fromEventDate and not(fromEventDate=eventDate)">
											<xsl:value-of select="$locale/announcements/adm/eventHasPeriod"/>
											<xsl:text>&#160;</xsl:text>
											<xsl:value-of select="fromEventDate"/>
											<xsl:value-of select="$locale/announcements/adm/to"/>
											<xsl:value-of select="eventDate"/>
											<xsl:text>. </xsl:text>
											<xsl:call-template name="declension">
												<xsl:with-param name="number" select="fromToEventDiff"/>
												<xsl:with-param name="dec_node_name" select="string('days')"/>
											</xsl:call-template>
											<br/>
											<xsl:value-of select="$locale/announcements/adm/startIn"/>
											<xsl:text>&#160;</xsl:text>
											<xsl:call-template name="declension">
												<xsl:with-param name="number" select="statusInDays"/>
												<xsl:with-param name="dec_node_name" select="string('days')"/>
											</xsl:call-template>
											<xsl:text>.</xsl:text>
										</xsl:when>
										<xsl:otherwise>
											<xsl:value-of select="$locale/announcements/adm/eventWillHappen"/>
											<xsl:text>&#160;</xsl:text>
											<xsl:value-of select="$locale/announcements/adm/status/willBeIn"/>
											<xsl:text>&#160;</xsl:text>
											<xsl:call-template name="declension">
												<xsl:with-param name="number" select="statusInDays"/>
												<xsl:with-param name="dec_node_name" select="string('days')"/>
											</xsl:call-template>
											<xsl:text>. </xsl:text>
											<xsl:value-of select="eventDate"/>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="$locale/announcements/adm/eventWillHappen"/>
									<xsl:text>&#160;</xsl:text>
									<xsl:value-of select="$locale/announcements/adm/status/*[name()=$statusText]"/>
								</xsl:otherwise>
							</xsl:choose>
						</div>
						<div class="small grey">
							<xsl:value-of select="shortDescription" disable-output-escaping="yes"/>
						</div>
					</td>
					<td>
						<xsl:value-of select="beginDate"/>
						<xsl:value-of select="$locale/announcements/adm/to"/>
						<xsl:value-of select="endDate"/>
					</td>
					<td>
						<a href="/adm/users/edit/{user}/">
							<xsl:choose>
								<xsl:when test="not(userData/@nickname='')">
									<xsl:value-of select="userData/@nickname"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="userData/@email"/>
								</xsl:otherwise>
							</xsl:choose>
						</a>
						<xsl:if test="groupData">
							<xsl:text> / </xsl:text>
							<xsl:choose>
								<xsl:when test="not(group=1)">
									<a href="http://{groupData/@domain}.{/root/@hostname}">
										<xsl:value-of select="groupData/@name"/>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="groupData/@name"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
					</td>
					<td>
						<xsl:variable name="catId" select="category"/>
						<xsl:choose>
							<xsl:when test="$catId=0">
								<xsl:value-of select="$locale/announcements/adm/category/noCategory"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="/root/content/announcementsLast/announceCateroty/category[@id=$catId]/@name"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<div id="priorityValueView-{id}">
							<xsl:value-of select="priority"/>
						</div>
						<input type="hidden" id="priorityValue-{id}" value="{priority}"/>
						<div id="prioritySlider-{id}"></div>
						<div class="savePriorityButton" id="savePriorityButton-{id}">
							<input type="button" value="{$locale/adm/save}" onclick="announcementsUI.savePriority( {id} );"/>
						</div>

						<xsl:if test="visible=0">
							<div style="color: #e9967a;">
								<xsl:value-of select="$locale/announcements/adm/notVisible"/>
							</div>
						</xsl:if>
					</td>
					<td class="actions">
						<a href="/adm/announcements/edit/{id}/" class="action edit"></a>
						<a href="#" class="action down" onclick="announcementsUI.getPrioritySlider({id}, {priority});"></a>
						<a href="/adm/announcements/delete/{id}/" class="action delete ajaxer"></a>
					</td>
				</tr>
			</xsl:for-each>
		</table>

		<!-- ==================================================================================================================== -->

		<h3>
			<xsl:value-of select="$locale/announcements/adm/pastEvents"/>
		</h3>

		<table>
			<colgroup>
				<col/>
				<col/>
				<col style="width: 160px"/>
				<col style="width: 200px"/>
				<col/>
				<col/>
				<col style="width: 115px"/>
			</colgroup>
			<tr>
				<th></th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/event_desc"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/event_dates_past"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/users_groups"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/category/category"/>
				</th>
				<th>
					<xsl:value-of select="$locale/announcements/adm/priority"/>
				</th>
				<th></th>
			</tr>
			<xsl:for-each select="announcements/event[status='past']">
				<tr>
					<td>
						<xsl:value-of select="position()"/>
					</td>
					<td>
						<xsl:variable name="statusText" select="status"/>

						<strong>
							<xsl:value-of select="title"/>
						</strong>
						<div class="eventDate">
							<xsl:value-of select="$locale/announcements/adm/eventInPastHappen"/>
							<xsl:text> </xsl:text>
							<xsl:value-of select="eventDate"/>
						</div>
						<div class="small grey">
							<xsl:value-of select="shortDescription" disable-output-escaping="yes"/>
						</div>
					</td>
					<td>
						<xsl:value-of select="beginDate"/>
						<xsl:value-of select="$locale/announcements/adm/to"/>
						<xsl:value-of select="endDate"/>
					</td>
					<td>
						<a href="/adm/users/edit/{user}/">
							<xsl:choose>
								<xsl:when test="not(userData/@nickname='')">
									<xsl:value-of select="userData/@nickname"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="userData/@email"/>
								</xsl:otherwise>
							</xsl:choose>
						</a>
						<xsl:if test="groupData">
							<xsl:text>, </xsl:text>
							<xsl:choose>
								<xsl:when test="not(group=1)">
									<a href="http://{groupData/@domain}.{/root/@hostname}">
										<xsl:value-of select="groupData/@name"/>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="groupData/@name"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
					</td>
					<td>
						<xsl:variable name="catId" select="category"/>
						<xsl:choose>
							<xsl:when test="$catId=0">
								<xsl:value-of select="$locale/announcements/adm/category/noCategory"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="/root/content/announcementsLast/announceCateroty/category[@id=$catId]/@name"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
					<td>
						<div id="priorityValueView-{id}">
							<xsl:value-of select="priority"/>
						</div>
						<input type="hidden" id="priorityValue-{id}" value="{priority}"/>
						<div id="prioritySlider-{id}"></div>
						<div class="savePriorityButton" id="savePriorityButton-{id}">
							<input type="button" value="{$locale/adm/save}" onclick="announcementsUI.savePriority( {id} );"/>
						</div>
						<xsl:if test="visible=0">
							<div style="color: #e9967a;">
								<xsl:value-of select="$locale/announcements/adm/notVisible"/>
							</div>
						</xsl:if>
					</td>
					<td class="actions">
						<a href="/adm/announcements/edit/{id}/" class="action edit"></a>
						<a href="#" class="action down" onclick="announcementsUI.getPrioritySlider({id}, {priority});"></a>
						<a href="/adm/announcements/delete/{id}/" class="action delete"></a>
					</td>
				</tr>
			</xsl:for-each>
		</table>

	</xsl:template>
</xsl:stylesheet>