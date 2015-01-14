<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="announcements-event-view">

		<div id="event-content" itemscope="itemscope" itemtype="http://schema.org/Event">

			<meta itemprop="url" content="http://{/root/@mainhost}/events/{event/link}"/>

			<div class="announce-big-title">
				<h2 itemprop="name">
					<xsl:value-of select="event/title"/>
				</h2>
			</div>
			<div class="announce-control">
				<xsl:if test="/root/auth/authorized and (/root/auth/authorized/@userid=event/user or /root/auth/authorized/@moderator=1)">
					<a href="/event/edit/{event/link}/">
						<xsl:value-of select="$locale/announcements/editEvent"/>
					</a>
				</xsl:if>
			</div>
			<div class="clear"/>

			<table class="announce-description">
				<tr>
					<td class="announce-image">
						<img src="http://{/root/@mainhost}/announcements/{event/id}-big.png" alt="{event/title}" itemprop="image"/>
					</td>
					<td class="announce-description">
						<div class="announce-title">
							<h3>
								<xsl:call-template name="announcements-dates">
									<xsl:with-param name="format" select="string( 'detailed' ) "/>
								</xsl:call-template>

								<xsl:if test="event/isoDate/@endDate">
									<xsl:choose>
										<xsl:when test="event/isoDate/@fromDate">
											<meta itemprop="startDate" content="{event/isoDate/@fromDate}"/>
											<meta itemprop="endDate" content="{event/isoDate/@endDate}"/>
										</xsl:when>
										<xsl:otherwise>
											<meta itemprop="endDate" content="{event/isoDate/@endDate}"/>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:if>
							</h3>
						</div>
						
						
						<xsl:choose>
							<xsl:when test="event/additionals/field[@alias='ticket-price']
									and event/additionals/field[@alias='ticket-price']/@value='0'">
								<div class="announce-price noRubles">
									<xsl:value-of select="$locale/announcements/freeEvent"/>
								</div>
							</xsl:when>
							<xsl:when test="event/additionals/field[@alias='ticket-price']">
								<div class="announce-price" itemprop="offers" itemscope="itemscope" itemtype="http://schema.org/AggregateOffer">
									<meta itemprop="priceCurrency" content="RUB"/>
									<meta itemprop="price" content="{event/additionals/field[@alias='ticket-price']/@value}"/>
									<xsl:value-of select="/root/content/announcements-event-view/additionalsFields/item[@alias='ticket-price']/@name"/>
									<xsl:text>:&#160;&#160;</xsl:text>
									<xsl:value-of select="event/additionals/field[@alias='ticket-price']/@value"/>
								</div>
							</xsl:when>
						</xsl:choose>

						<div class="clear"/>

						<xsl:if test="event/location-data">
							<div class="announce-location">
								<xsl:choose>
									<xsl:when test="not(event/location-data/@url='')">
										<a href="{event/location-data/@url}">
											<xsl:value-of select="event/location-data/@name"/>
										</a>
									</xsl:when>
									<xsl:otherwise>
										<span class="locationTitle">
											<xsl:value-of select="event/location-data/@name"/>
										</span>
									</xsl:otherwise>
								</xsl:choose>
								<span>
									<xsl:if test="not(event/location-data/@address='')">
										<xsl:value-of select="$locale/announcements/address"/>
										<xsl:text>&#160;</xsl:text>
										<span itemprop="location">
											<xsl:value-of select="event/location-data/@address"/>
										</span>
									</xsl:if>
									<xsl:if test="not(event/location-data/@address='') and not(event/location-data/@phone='')">
										<br/>
									</xsl:if>
									<xsl:if test="not(event/location-data/@phone='')">
										<xsl:value-of select="$locale/announcements/phone"/>
										<xsl:text>&#160;</xsl:text>
										<xsl:value-of select="event/location-data/@phone"/>
									</xsl:if>
									<xsl:if test="not(event/location-data/@info='')">
										<br/>
										<xsl:value-of select="event/location-data/@info"/>
									</xsl:if>
								</span>
							</div>
						</xsl:if>

						<div class="announce-text" itemprop="description">
							<xsl:value-of select="event/description" disable-output-escaping="yes"/>
						</div>

						<xsl:if test="event/schedules/item">
							<div class="announce-schedule">
								<h3>
									<xsl:value-of select="event/schedules/@title"/>
								</h3>
								<xsl:for-each select="event/schedules/item">
									<p>
										<xsl:value-of select="@name"/>
										<xsl:text>:&#160;</xsl:text>
										<xsl:value-of select="@value"/>
									</p>
								</xsl:for-each>
							</div>
						</xsl:if>
					</td>
				</tr>
			</table>

			<div class="announce-image plane">
				<img src="/announcements/{event/id}-big.png" alt="{event/title}"/>
			</div>

			<div class="announce-title plane">
				<h3>
					<xsl:call-template name="announcements-dates">
						<xsl:with-param name="format" select="string( 'detailed' ) "/>
					</xsl:call-template>
				</h3>
			</div>

			<xsl:if test="event/location-data">
				<div class="announce-location plane">
					<xsl:choose>
						<xsl:when test="not(event/location-data/@url='')">
							<a href="{event/location-data/@url}">
								<xsl:value-of select="event/location-data/@name"/>
							</a>
						</xsl:when>
						<xsl:otherwise>
							<span class="locationTitle">
								<xsl:value-of select="event/location-data/@name"/>
							</span>
						</xsl:otherwise>
					</xsl:choose>
					<span>
						<xsl:if test="not(event/location-data/@address='')">
							<xsl:value-of select="$locale/announcements/address"/>
							<xsl:value-of select="event/location-data/@address"/>
						</xsl:if>
						<xsl:if test="not(event/location-data/@address='') and not(event/location-data/@phone='')">
							<br/>
						</xsl:if>
						<xsl:if test="not(event/location-data/@phone='')">
							<xsl:value-of select="$locale/announcements/phone"/>
							<xsl:value-of select="event/location-data/@phone"/>
						</xsl:if>
					</span>
					<span>
						<xsl:if test="not(event/location-data/@info='')">
							<xsl:value-of select="event/location-data/@info"/>
						</xsl:if>
					</span>
				</div>
			</xsl:if>

			<xsl:if test="event/additionals/field[@alias='ticket-price']">
				<div class="announce-price plane">
					<xsl:value-of select="/root/content/announcements-event-view/additionalsFields/item[@alias='ticket-price']/@name"/>
					<xsl:text>:&#160;&#160;</xsl:text>
					<xsl:value-of select="event/additionals/field[@alias='ticket-price']/@value"/>
				</div>
			</xsl:if>

			<div class="announce-text plane">
				<xsl:value-of select="event/description" disable-output-escaping="yes"/>
			</div>

		</div>

	</xsl:template>
</xsl:stylesheet>
