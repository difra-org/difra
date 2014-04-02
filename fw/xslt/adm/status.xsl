<?xml version="1.0" encoding="UTF-8"?>
<!--
This software cannot be used, distributed or modified, completely or partially, without written permission by copyright holder.
Copyright © A-Jam Studio
License: http://ajamstudio.com/difra/license
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:template match="status">
		<h1>
			<xsl:value-of select="$locale/adm/stats/h2"/>
		</h1>

		<h2>Difra</h2>
		<table class="summary">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<tbody>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/summary/platform-version"/>
					</th>
					<td>
						<xsl:value-of select="@difra"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/summary/loaded-plugins"/>
					</th>
					<td>
						<xsl:choose>
							<xsl:when test="@enabledPlugins and not(@enabledPlugins='')">
								<xsl:value-of select="@enabledPlugins"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>—</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/summary/cache-type"/>
					</th>
					<td>
						<xsl:value-of select="@cache"/>
					</td>
				</tr>
			</tbody>
		</table>
		<h2>
			<xsl:value-of select="$locale/adm/stats/server/title"/>
		</h2>
		<table class="summary">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<tbody>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/server/webserver"/>
					</th>
					<td>
						<xsl:value-of select="@webserver"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/server/phpversion"/>
					</th>
					<td>
						<xsl:value-of select="@phpversion"/>
					</td>
				</tr>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/permissions"/>
					</th>
					<td>
						<xsl:choose>
							<xsl:when test="stats/permissions/@*">
								<xsl:for-each select="stats/permissions/@*">
									<div style="color:red">
										<xsl:value-of select="."/>
									</div>
								</xsl:for-each>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="$locale/adm/stats/permissions-ok"/>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</tbody>
		</table>
		<h2>
			<xsl:value-of select="$locale/adm/stats/extensions/title"/>
		</h2>
		<table class="summary">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<tbody>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/extensions/required-extensions"/>
					</th>
					<td>
						<xsl:choose>
							<xsl:when test="not(extensions/@ok='')">
								<xsl:value-of select="extensions/@ok"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>—</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
				<xsl:if test="not(extensions/@required='')">
					<tr>
						<th>
							<xsl:value-of select="$locale/adm/stats/extensions/missing-extensions"/>
						</th>
						<td style="color:red">
							<xsl:value-of select="extensions/@required"/>
						</td>
					</tr>
				</xsl:if>
				<tr>
					<th>
						<xsl:value-of select="$locale/adm/stats/extensions/extra-extensions"/>
					</th>
					<td>
						<xsl:choose>
							<xsl:when test="not(extensions/@extra='')">
								<xsl:value-of select="extensions/@extra"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>—</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</tbody>
		</table>
		<xsl:apply-templates select="unify" mode="stats"/>
		<h2>
			<xsl:value-of select="$locale/adm/stats/database/title"/>
		</h2>
		<xsl:choose>
			<xsl:when test="stats/mysql/@error">
				<div class="error">
					<xsl:value-of select="stats/mysql/@error"/>
				</div>
			</xsl:when>
			<xsl:when test="count(stats/mysql/table[@diff=1])=0 and count(stats/mysql/table[@nodef=1])=0 and count(stats/mysql/table[@nogoal=1])=0">
				<div class="message">
					<xsl:value-of select="$locale/adm/stats/database/status-ok"/>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="stats/mysql/table" mode="diff"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="stats/mysql/table" mode="diff">
		<xsl:choose>
			<xsl:when test="@diff=1">
				<table>
					<colgroup>
						<col style="width:250px"/>
						<col/>
					</colgroup>
					<tbody>
						<tr>
							<td colspan="2">
								<xsl:text>Table </xsl:text>
								<strong>`<xsl:value-of select="@name"/>`
								</strong>
								<xsl:text> diff:</xsl:text>

							</td>
						</tr>
						<tr>
							<td style="width:50%">Current</td>
							<td>Described</td>
						</tr>
						<xsl:for-each select="diff">
							<xsl:choose>
								<xsl:when test="@sign='='">
									<tr class="small bg-green">
										<td>
											<xsl:value-of select="@value"/>
										</td>
										<td>
											<xsl:value-of select="@value"/>
										</td>
									</tr>
								</xsl:when>
								<xsl:when test="@sign='-'">
									<tr class="small bg-red">
										<td>
											<xsl:value-of select="@value"/>
										</td>
										<td>
										</td>
									</tr>
								</xsl:when>
								<xsl:when test="@sign='+'">
									<tr class="small bg-red">
										<td>
										</td>
										<td>
											<xsl:value-of select="@value"/>
										</td>
									</tr>
								</xsl:when>
							</xsl:choose>
						</xsl:for-each>
					</tbody>
				</table>
			</xsl:when>
			<xsl:when test="@nogoal=1">
				<div class="message error">
					<xsl:text>Table `</xsl:text>
					<xsl:value-of select="@name"/>
					<xsl:text>` is not described.</xsl:text>
				</div>
			</xsl:when>
			<xsl:when test="@nodef=1">
				<div class="message error">
					<xsl:text>Table `</xsl:text>
					<xsl:value-of select="@name"/>
					<xsl:text>` does not exist.</xsl:text>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<div class="message">
					<xsl:text>Table `</xsl:text>
					<xsl:value-of select="@name"/>
					<xsl:text>` is ok.</xsl:text>
				</div>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="unify" mode="stats">
		<h2>
			<xsl:value-of select="$locale/adm/stats/unify/title"/>
		</h2>
		<table class="unify">
			<colgroup>
				<col style="width:250px"/>
				<col/>
			</colgroup>
			<xsl:for-each select="*">
				<tr>
					<td>
						<xsl:value-of select="name()"/>
					</td>
					<td>
						<xsl:choose>
							<xsl:when test="@status='ok'">ok</xsl:when>
							<xsl:when test="@status='missing'">
								<a href="/adm/status/unify/create/{name()}" class="ajaxer">create</a>
							</xsl:when>
							<xsl:when test="@status='alter'">
								<a href="/adm/status/unify/alter/{name()}" class="ajaxer">
									<xsl:text>alter table (</xsl:text>
									<xsl:value-of select="@action"/>
									<xsl:text>): </xsl:text>
									<xsl:value-of select="@sql"/>
								</a>
							</xsl:when>
							<xsl:otherwise>?</xsl:otherwise>
						</xsl:choose>
					</td>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>
</xsl:stylesheet>
