<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template name="blogsPosts">
		<xsl:choose>
			<xsl:when test="post">
				<xsl:variable name="link" select="@link"/>
				<xsl:for-each select="post">
					<div class="blogPost">
						<div class="blogPost-header">
							<xsl:if test="@canModify=1">
								<div class="blogPost-controls">
									<a href="/blogs/post/edit/{@id}"
									   class="button"
									   title="Редактирование поста">
										<div class="icon-edit"/>
									</a>
									<a href="#" class="button" title="Удалить пост" onclick="blogs.deleteNotify( {@id} );">
										<div class="sostav-icon-2"></div>
									</a>
								</div>
							</xsl:if>
							<div class="blogPost-avatar">
								<img src="/avatars/users/{@user}s.png" alt=""/>
							</div>
							<div class="blogPost-title">
								<h3>
									<a href="{@url}">
										<xsl:value-of select="@title"/>
									</a>
								</h3>
							</div>
							
							<div class="blogPost-author small grey">
								<a href="{/root/@urlprefix}/blogs/{@nickname}">
									<xsl:value-of select="@nickname"/>
								</a>
								<xsl:if test="@groupDomain and not(@groupDomain='')">
									<xsl:text> → </xsl:text>
									<a>
										<xsl:attribute name="href">
											<xsl:choose>
												<xsl:when test="@groupDomain='musiq'">
													<xsl:value-of select="/root/@urlPrefix"/>
													<xsl:text>/</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>http://</xsl:text>
													<xsl:value-of select="@groupDomain"/>
													<xsl:text>.</xsl:text>
													<xsl:value-of select="/root/@mainhost"/>
													<xsl:text>/</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										<xsl:value-of select="@groupName"/>
									</a>
								</xsl:if>
								<xsl:text>&#160;•&#160;</xsl:text>
								<xsl:value-of select="@date"/>
								<xsl:text>&#160;•&#160;</xsl:text>
								<xsl:call-template name="declension">
									<xsl:with-param name="number" select="@comments"/>
									<xsl:with-param name="view_number" select="1"/>
									<xsl:with-param name="dec_node_name" select="string('comments')"/>
								</xsl:call-template>
							</div>
							<div style="clear:both;"/>
						</div>
						<div class="blogPost-text">
							<xsl:value-of select="@preview" disable-output-escaping="yes"/>
							<span class="read-more">
								<xsl:text> </xsl:text>
								<a href="{@url}">
									<xsl:text>→</xsl:text>
								</a>
							</span>
						</div>
					</div>
				</xsl:for-each>
				<xsl:call-template name="paginator">
					<xsl:with-param name="pages" select="@pages"/>
					<xsl:with-param name="current" select="@current"/>
					<xsl:with-param name="link" select="$link"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<p class="empty">Не найдено ни одной записи.</p>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>