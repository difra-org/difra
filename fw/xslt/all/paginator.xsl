<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="paginator">
		<xsl:param name="i" select="1"/>
		<xsl:param name="pages"/>
		<xsl:param name="link"/>
		<xsl:param name="current"/>

		<xsl:if test="$pages &gt; 1 or $current != 1">
			<!-- кнопка назад -->
			<xsl:if test="$i = 1 and $current &gt; 1">
				<div class="pagerItem pagerPrev">
					<xsl:variable name="prev" select="$current - 1"/>
					<xsl:choose>
						<xsl:when test="$prev = 1">
							<a href="{$link}">←</a>
						</xsl:when>
						<xsl:otherwise>
							<a href="{$link}/page/{$prev}">←</a>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:if>

			<div class="pagerItem" id="pagerItem{$i}">
				<xsl:choose>
					<xsl:when test="$i = $current">
						<xsl:attribute name="class">pagerItem pagerSelected</xsl:attribute>
						<xsl:value-of select="$i"/>
					</xsl:when>
					<xsl:when test="$i = 1">
						<a href="{$link}">
							<xsl:value-of select="$i"/>
						</a>
					</xsl:when>
					<xsl:otherwise>
						<a href="{$link}/page/{$i}">
							<xsl:value-of select="$i"/>
						</a>
					</xsl:otherwise>
				</xsl:choose>
			</div>

			<xsl:choose>
				<!-- скипаем от первого в середину -->
				<xsl:when test="$i &lt; $current - 6">
					<div class="pagerItem pagerSkip">…</div>
					<xsl:call-template name="paginator">
						<xsl:with-param name="pages" select="$pages"/>
						<xsl:with-param name="current" select="$current"/>
						<xsl:with-param name="link" select="$link"/>
						<xsl:with-param name="i" select="$current - 4"/>
					</xsl:call-template>
				</xsl:when>
				<!-- скипаем от средних до последних -->
				<xsl:when test="$i &gt; $current + 3 and $i &lt; $pages">
					<div class="pagerItem pagerSkip">…</div>
					<xsl:call-template name="paginator">
						<xsl:with-param name="pages" select="$pages"/>
						<xsl:with-param name="current" select="$current"/>
						<xsl:with-param name="link" select="$link"/>
						<xsl:with-param name="i" select="$pages"/>
					</xsl:call-template>
				</xsl:when>
				<!-- показываемые страницы -->
				<xsl:when test="$i &lt; $pages">
					<xsl:call-template name="paginator">
						<xsl:with-param name="pages" select="$pages"/>
						<xsl:with-param name="current" select="$current"/>
						<xsl:with-param name="link" select="$link"/>
						<xsl:with-param name="i" select="$i + 1"/>
					</xsl:call-template>
				</xsl:when>
				<!-- кнопка вперёд -->
				<xsl:otherwise>
					<xsl:if test="$current &lt; $pages">
						<div class="pagerItem pagerNext">
							<xsl:variable name="next" select="$current + 1"/>
							<a href="{$link}/page/{$next}">→</a>
						</div>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
