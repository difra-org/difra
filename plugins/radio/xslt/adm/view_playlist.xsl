<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

	<xsl:template match="playlist-view">
		<h2>
			<a href="/adm/radio/channels">
				<xsl:value-of select="$locale/radio/viewTitle"/>
			</a>
			<xsl:text> → </xsl:text>
			<xsl:value-of select="$locale/radio/playListTitle"/>«<xsl:value-of select="channel/@name"/>»
		</h2>
		<div class="radioPlayList">
			<h3>
				<xsl:value-of select="$locale/radio/playList"/>
				<xsl:variable name="status" select="channel/@status"/>
				<span style="float: right; color: {$locale/radio/status/*[name()=$status]/@color}">
					<xsl:if test="not(channel/@status='')">
						статус:
						<xsl:value-of select="$locale/radio/status/*[name()=$status]"/>
					</xsl:if>,
					<a href="http://radio.{/root/@mainhost}:8000/{channel/@name}.m3u">cлушать</a>
				</span>
			</h3>
			<ul class="playListUL">
				<li>
					<xsl:choose>
						<xsl:when test="channel/currentPlay/@title">
							<h2 class="radioTitle">
								<xsl:value-of select="channel/currentPlay/@title"/>
								<span class="gray">
									<xsl:text>&#160;</xsl:text>
									<xsl:value-of select="channel/currentPlay/@duration"/>
								</span>
							</h2>
							<div class="radioTimer">
								<div id="countdown"></div>
							</div>

						</xsl:when>
						<xsl:otherwise>
							Ничего не играет.
						</xsl:otherwise>
					</xsl:choose>
				</li>
			</ul>
			<ul id="activePlayList" class="libratyUL connectedSortable playpist-container">
				<xsl:for-each select="item">
					<li id="{@id}_{position()}{@id}">
						<xsl:value-of select="@title"/>
						<span class="gray">
							<xsl:text>&#160;</xsl:text>
							<xsl:value-of select="@duration"/>
						</span>
					</li>
				</xsl:for-each>

			</ul>
			<input class="button" value="Сохранить плейлист" onclick="javascript: savePlayList('{channel/@name}');"/>




		</div>

		<div class="radioLibrary">
			<div class="radioTabs">
				<ul>
					<li id="libraryTab" class="selectedTab" onclick="javascript: changeTab( 'library' );">
						<xsl:value-of select="$locale/radio/library"/>
					</li>
					<li id="historyTab" onclick="javascript: changeTab( 'history' );">
						<xsl:text>Последние исполнители</xsl:text>
					</li>
				</ul>
			</div>
			<div style="clear:both;"/>
			<div id="radioLibrary">
				<xsl:choose>
				<xsl:when test="library/empty">
					<xsl:value-of select="$locale/radio/emptyLibrary"/>
				</xsl:when>
				<xsl:otherwise>
					<div class="radioSort">
						<xsl:text>Сортировать: &#160; </xsl:text>
						<xsl:choose>
							<xsl:when test="library/@sort='last'">
								<b>
									<xsl:text>по последнему проигрыванию</xsl:text>
								</b>
							</xsl:when>
							<xsl:otherwise>
								<a href="/adm/radio/playlist/{channel/@name}/sort/last/">по последнему
									проигрыванию
								</a>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> &#160; </xsl:text>
						<xsl:choose>
							<xsl:when test="library/@sort='weight'">
								<b>
									<xsl:text>по весу</xsl:text>
								</b>
							</xsl:when>
							<xsl:otherwise>
								<a href="/adm/radio/playlist/{channel/@name}/sort/weight/">по весу</a>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text> &#160; </xsl:text>
						<xsl:choose>
							<xsl:when test="library/@sort='name'">
								<b>
									<xsl:text>по названию</xsl:text>
								</b>
							</xsl:when>
							<xsl:otherwise>
								<a href="/adm/radio/playlist/{channel/@name}/sort/name/">по названию</a>
							</xsl:otherwise>
						</xsl:choose>
					</div>

					<ul class="libratyUL connectedSortable scrollLibrary library-container" id="library">
						<xsl:for-each select="library/track">
							<li id="{@id}_{position()}">
								<xsl:value-of select="@name"/> -
								<xsl:value-of select="@title"/>
								<span class="gray">
									<xsl:text>&#160;</xsl:text><xsl:value-of select="@duration"/>
								</span>
								<div class="radioEditIcons">
									<a href="#"
									   class="action view"
									   onclick="javascript: showEdit({@id});">Просмотр
									</a>
									<a href="/adm//radio/deletetrack/{/root/playlist-view/channel/@name}/{@id}/"
									   class="ajaxer action delete">Удалить
									</a>
								</div>
								<div class="small little_spacing">
									<span>
										<xsl:choose>
											<xsl:when test="@tdiff&lt;/root/playlist-view/library/@minSongInQuery">
												<xsl:attribute name="style">
													<xsl:text>color: #8b0000; font-weight: bold;</xsl:text>
												</xsl:attribute>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="style">
													<xsl:text>color: #2f4f4f; font-weight: bold;</xsl:text>
												</xsl:attribute>
											</xsl:otherwise>

										</xsl:choose>
										Проигрывался:
										<xsl:choose>
											<xsl:when test="not(@tdiff='')">
												<xsl:value-of select="@tdiff"/> минут назад
											</xsl:when>
											<xsl:otherwise>
												<xsl:text>никогда</xsl:text>
											</xsl:otherwise>
										</xsl:choose>
									</span>
								</div>
								<div id="trackInfo-{@id}" class="trackInfo">
									<form method="post"
									      action="/adm/radio/settracksettings/{/root/playlist-view/channel/@name}/{@id}/"
									      class="ajaxer">
										<div class="small gray little_spacing">
											Тэги:
											<xsl:value-of select="@tags"/>
										</div>
										<div class="small gray little_spacing">
											Последний раз в эфире:
											<xsl:value-of select="@lastPlayed"/>
										</div>
										<div class="small gray little_spacing">
											Проигрываний трека:
											<xsl:value-of select="@played"/>
										</div>
										<div class="small gray little_spacing">
											Значение plays:
											<xsl:value-of select="@plays"/>
										</div>
										<label>
											Вес композиции:
										</label>
										<input type="text" name="weight" value="{@weight}"/>
										<input type="submit" value="Изменить вес"/>
									</form>
								</div>
								<div style="clear: both;"/>
							</li>
						</xsl:for-each>
					</ul>
				</xsl:otherwise>
			</xsl:choose>
			</div>

			<div id="historyLibrary">
				<ul class="libratyUL scrollLibrary artist-container">
					<xsl:choose>
						<xsl:when test="artistHistory/empty">
							<li>Ничего не проигрывалось</li>
						</xsl:when>
						<xsl:otherwise>
							<xsl:for-each select="artistHistory/artist">
								<li>
									<div class="radioTitle">
										<xsl:value-of select="@name"/>
									</div>
									<div style="clear: both;"/>
									<div class="small little_spacing">
										Последний раз в эфире:
										<xsl:value-of select="@lastPlayedArtist"/>
										<xsl:text>,&#160;</xsl:text>
										<span style="color: #8b0000;">
											<xsl:choose>
												<xsl:when
													test="@tdiff&lt;/root/playlist-view/artistHistory/@minArtistInQuery">
													<xsl:attribute name="style">
														<xsl:text>color: #8b0000; font-weight: bold;</xsl:text>
													</xsl:attribute>
												</xsl:when>
												<xsl:otherwise>
													<xsl:attribute name="style">
														<xsl:text>color: #2f4f4f; font-weight: bold;</xsl:text>
													</xsl:attribute>

												</xsl:otherwise>

											</xsl:choose>
											<xsl:value-of select="@tdiff"/> минут назад
										</span>
									</div>
								</li>
							</xsl:for-each>
						</xsl:otherwise>
					</xsl:choose>
				</ul>
			</div>


		</div>

		<script type="text/javascript">
			startEditList();
			<xsl:if test="channel/currentPlay/@stop">
				stopTime = <xsl:value-of select="channel/currentPlay/@stop"/>;
				startCounter( stopTime );
			</xsl:if>
		</script>

	</xsl:template>
</xsl:stylesheet>

