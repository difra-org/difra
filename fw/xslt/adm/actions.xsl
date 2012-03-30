<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:variable name="actionSpacer"> &#160; </xsl:variable>

	<xsl:template name="actionEdit">
		<xsl:param name="link"/>
		<a href="{$link}" class="action edit">
			<xsl:value-of select="$locale/actions/edit"/>
		</a>
		<xsl:value-of select="$actionSpacer"/>
	</xsl:template>

	<xsl:template name="actionDelete">
		<xsl:param name="link"/>
		<a href="{$link}" class="action delete ajaxer">
			<xsl:value-of select="$locale/actions/delete"/>
		</a>
		<xsl:value-of select="$actionSpacer"/>
	</xsl:template>

	<xsl:template name="actionUp">
		<xsl:param name="link"/>
		<xsl:param name="idPrefix"/>
		<a>
			<xsl:choose>
				<xsl:when test="position()=1">
					<xsl:attribute name="href">#</xsl:attribute>
					<xsl:attribute name="class">action up disabled ajaxer</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="href">
						<xsl:value-of select="$link"/>
					</xsl:attribute>
					<xsl:attribute name="class">action up ajaxer</xsl:attribute>
					<xsl:attribute name="onmouseover">
						<xsl:text>$('#</xsl:text>
						<xsl:value-of select="$idPrefix"/>
						<xsl:value-of select="position()-1"/>
						<xsl:text>').addClass('moveUpHighlight')</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="onmouseout">
						<xsl:text>$('#</xsl:text>
						<xsl:value-of select="$idPrefix"/>
						<xsl:value-of select="position()-1"/>
						<xsl:text>').removeClass('moveUpHighlight')</xsl:text>
					</xsl:attribute>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:value-of select="$locale/actions/up"/>
		</a>
		<xsl:value-of select="$actionSpacer"/>
	</xsl:template>

	<xsl:template name="actionDown">
		<xsl:param name="link"/>
		<xsl:param name="idPrefix"/>
		<a>
			<xsl:choose>
				<xsl:when test="position()=last()">
					<xsl:attribute name="href">#</xsl:attribute>
					<xsl:attribute name="class">action down disabled ajaxer</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="href">
						<xsl:value-of select="$link"/>
					</xsl:attribute>
					<xsl:attribute name="class">action down ajaxer</xsl:attribute>
					<xsl:attribute name="onmouseover">
						<xsl:text>$('#</xsl:text>
						<xsl:value-of select="$idPrefix"/>
						<xsl:value-of select="position()+1"/>
						<xsl:text>').addClass('moveDownHighlight')</xsl:text>
					</xsl:attribute>
					<xsl:attribute name="onmouseout">
						<xsl:text>$('#</xsl:text>
						<xsl:value-of select="$idPrefix"/>
						<xsl:value-of select="position()+1"/>
						<xsl:text>').removeClass('moveDownHighlight')</xsl:text>
					</xsl:attribute>
				</xsl:otherwise>
				<xsl:value-of select="$locale/actions/down"/>
			</xsl:choose>
		</a>
		<xsl:value-of select="$actionSpacer"/>
	</xsl:template>

</xsl:stylesheet>