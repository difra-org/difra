<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/mail">
		<fromtext>
			<xsl:value-of select="/mail/locale/auth/mail/from"/>
		</fromtext>

		<subject>
			<xsl:value-of select="/mail/locale/comments/mail/on"/>
			<xsl:text>«</xsl:text>
			<xsl:value-of select="@mainHost"/>
			<xsl:text>»</xsl:text>

			<xsl:choose>
				<xsl:when test="@replay and @replay='1'">
					<xsl:value-of select="/mail/locale/comments/mail/newReplySubject"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="@module='albums'">
							<xsl:value-of select="/mail/locale/comments/mail/newCommentAlbumSubject"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="/mail/locale/comments/mail/newCommentPostSubject"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</subject>
		<text>
			<div style="margin: 0px; font-size: 13px; line-height: 13px; font-family: Arial, serif;">

				<h2 style="margin: 0 0 10px; font-size: 15px;">
					<xsl:choose>
						<xsl:when test="@reply and @reply='1'">
							<xsl:value-of select="/mail/locale/comments/mail/replyTitle"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="/mail/locale/comments/mail/newCommentTitle"/>
						</xsl:otherwise>
					</xsl:choose>
				</h2>

				<hr style="border-width: 0; background-color: #afaeae; height: 1px; margin: 0 0 10px;"/>

				<p style="color: #919191; margin: 0 0 10px;">
					<xsl:value-of select="@reply_nickname"/>
					<xsl:choose>
						<xsl:when test="@module='albums'">
							<xsl:choose>
								<xsl:when test="@replay and @replay='1'">
									<xsl:value-of select="/mail/locale/comments/mail/albumReply"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/mail/locale/comments/mail/albumComment"/>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:text>«</xsl:text>
							<a href="{@link}" style="color: #919191;">
								<xsl:value-of select="@title"/>
							</a>
							<xsl:text>».</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="@replay and @replay='1'">
									<xsl:value-of select="/mail/locale/comments/mail/postReply"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="/mail/locale/comments/mail/postComment"/>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:text>«</xsl:text>
							<a href="{@link}" style="color: #919191;">
								<xsl:value-of select="@title"/>
							</a>
							<xsl:text>».</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</p>

				<xsl:if test="@replay and @replay='1'">
					<div style="margin: 0 0 10px; padding: 10px; background-color: #f0f0e6; border-radius: 6px;">
						<h3 style="color: #000000; margin: 0 0 8px; font-size: 13px;">
							<xsl:value-of select="/mail/locale/comments/mail/yourComment"/>
							<xsl:text>:</xsl:text>
						</h3>
						<p style="color: #4c4c4c; margin: 0;">
							<xsl:value-of select="@original"/>
						</p>
					</div>
				</xsl:if>

				<div style="margin: 0; padding: 10px; background-color: #f0f0e6; border-radius: 6px;">
					<h3 style="color: #000000; margin: 0 0 8px; font-size: 13px;">
						<xsl:value-of select="@reply_nickname"/>
						<xsl:text>:</xsl:text>
					</h3>
					<p style="color: #4c4c4c; margin: 0 0 10px; ">
						<xsl:value-of select="@message"/>
					</p>
					<p style="margin: 0 0 3px;">
						<a href="{@link}" style="color: #016baf;">
							<xsl:value-of select="/mail/locale/comments/mail/reply"/>
						</a>
					</p>
				</div>

				<div style="margin: 10px 0 0; ">
					<xsl:value-of select="/mail/locale/comments/mail/unsubscribe"/>
					<a href="{@unsubscribe}" style="color: #016baf;">
						<xsl:value-of select="/mail/locale/comments/mail/unsubscribeLink"/>
					</a>.
				</div>
			</div>
		</text>
	</xsl:template>
</xsl:stylesheet>
