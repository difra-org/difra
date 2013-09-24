<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="announcements-dates">
		<xsl:param name="format" select="string( 'cut' )"/>

		<xsl:variable name="day" select="concat( 'day_', eventDate/@w )"/>
		<xsl:variable name="fullDay" select="concat( 'fullDay_', event/eventDate/@w )"/>
		<xsl:variable name="month" select="concat( 'month_', eventDate/@m) "/>
		<xsl:variable name="fullMonth" select="concat( 'month_', event/eventDate/@m)"/>
		<xsl:variable name="year" select="eventDate/@Y"/>
		<xsl:variable name="fullYear" select="event/eventDate/@Y"/>

		<xsl:choose>
			<xsl:when test="$format='cut'">

				<xsl:choose>
					<xsl:when test="fromEventDate and not(fromEventDate=eventDate)">
						<xsl:variable name="fromMonth" select="concat( 'month_', fromEventDate/@m) "/>

						<xsl:value-of select="fromEventDate/@d"/>
						<xsl:text>&#160;</xsl:text>
						<xsl:value-of select="$locale/announcements/dates/months/*[name()=$fromMonth ]/text()"/>
						<xsl:text> — </xsl:text>
						<xsl:value-of select="eventDate/@d"/>
						<xsl:text>&#160;</xsl:text>
						<xsl:value-of select="$locale/announcements/dates/months/*[name()=$month ]/text()"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$locale/announcements/dates/weekdays/*[name()=$day ]/text()"/>
						<xsl:text>, </xsl:text>
						<xsl:value-of select="eventDate/@d"/>
						<xsl:text>&#160;</xsl:text>
						<xsl:value-of select="$locale/announcements/dates/months/*[name()=$month ]/text()"/>

						<xsl:if test="not(/root/date/@Y=$year)">
							<xsl:text>&#160;</xsl:text>
							<xsl:value-of select="$year"/>
						</xsl:if>

						<xsl:if test="additionals/field[@alias='eventTime']">
							<xsl:text>, </xsl:text>
							<xsl:value-of select="additionals/field[@alias='eventTime']/@value"/>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>

			<xsl:when test="$format='detailed'">
				<xsl:choose>
					<xsl:when test="event/fromEventDate and not(event/fromEventDate=event/eventDate)">
						<xsl:variable name="fromMonth" select="concat( 'month_', event/fromEventDate/@m) "/>

						<xsl:value-of select="event/fromEventDate/@d"/>
						<xsl:text>&#160;</xsl:text>
						<xsl:value-of select="$locale/announcements/dates/months/*[name()=$fromMonth ]/text()"/>
						<xsl:text> — </xsl:text>
						<xsl:value-of select="event/eventDate/@d"/>
						<xsl:text>&#160;</xsl:text>
						<xsl:value-of select="$locale/announcements/dates/months/*[name()=$fullMonth ]/text()"/>

						<xsl:if test="not(/root/date/@Y=$fullYear)">
							<xsl:text>&#160;</xsl:text>
							<xsl:value-of select="$fullYear"/>
						</xsl:if>

					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$locale/announcements/dates/weekdays/*[name()=$fullDay]/text()"/>
						<xsl:text>, </xsl:text>
						<xsl:value-of select="event/eventDate/@d"/>
						<xsl:text>&#160;</xsl:text>
						<xsl:value-of select="$locale/announcements/dates/months/*[name()=$fullMonth ]/text()"/>

						<xsl:if test="not(/root/date/@Y=$fullYear)">
							<xsl:text>&#160;</xsl:text>
							<xsl:value-of select="$fullYear"/>
						</xsl:if>

						<xsl:if test="event/additionals/field[@alias='eventTime']">
							<xsl:text>&#160;</xsl:text>
							<xsl:value-of select="$locale/announcements/in"/>
							<xsl:text>&#160;</xsl:text>
							<xsl:value-of select="event/additionals/field[@alias='eventTime']/@value"/>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>

		</xsl:choose>


	</xsl:template>
</xsl:stylesheet>
