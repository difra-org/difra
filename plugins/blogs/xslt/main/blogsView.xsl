<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="blogsView">
		<div class="blogsView">
			<xsl:if test="not(@name='musiq')">
				<div class="tabs" id="blogsTabs">

					<xsl:choose>
						<xsl:when test="/root/blogsView/@group and not(/root/blogsView/@group='')">
							<ul>
								<a href="/"><li id="blogs-1">Страница группы</li></a>
								<li id="blogs-2" class="selected">Блог</li>
							</ul>
						</xsl:when>
						<xsl:otherwise>
							<ul>
								<li class="selected">Блог</li>
								<xsl:if test="/root/auth/authorized/@userid=/root/blogsView/@user">
									<a href="http://{/root/@mainhost}/favorites/{/root/user/usersAdditionals/@nickname}">
										<li>Избранные блоги</li>
									</a>
								</xsl:if>
							</ul>
						</xsl:otherwise>
					</xsl:choose>
				</div>
			</xsl:if>
			<h2>
				<xsl:choose>
					<xsl:when test="@name='musiq'">
						<xsl:text>Новости</xsl:text>
					</xsl:when>
				</xsl:choose>
			</h2>
			<xsl:call-template name="blogsPosts"/>
		</div>
	</xsl:template>
</xsl:stylesheet>
