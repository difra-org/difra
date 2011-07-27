<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE xsl:stylesheet [
<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "xhtml-lat1.ent">
<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "xhtml-symbol.ent">
<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "xhtml-special.ent">
%lat1;
%symbol;
%special;
]>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
	xmlns:dyn="http://exslt.org/dynamic"
	extension-element-prefixes="dyn">
	<xsl:template match="form">

		<xsl:variable name="form_name" select="@name"/>
		<xsl:variable name="lang_form" select="$locale/*[name()=concat('form_',$form_name)]"/>
		<xsl:variable name="required_str"><span class="required" style="color:#ff0000">* </span></xsl:variable>

		<div id="{@div}">
			<form action="{@action}" method="post" id="form_{@name}">
				<xsl:if test="@enctype">
					<xsl:attribute name="enctype"><xsl:value-of select="@enctype"/></xsl:attribute>
				</xsl:if>
				
				<xsl:variable name="form_topic" select="concat(@div,'_topic')"/>
				<xsl:if test="$lang_form/*[name()=$form_topic]">
					<div class="form_topic"><xsl:value-of select="$lang_form/*[name()=$form_topic]"/></div>
				</xsl:if>
				
				<xsl:for-each select="*">
					<xsl:variable name="required">
						<xsl:if test="@error_required or @error_type or @error_type_date">
							<xsl:copy-of select="$required_str"/>
						</xsl:if>
					</xsl:variable>

					<xsl:choose>

						<!-- pass through data -->
						<xsl:when test="name()='data'">
							<xsl:for-each select="item">
								<input type="hidden" name="{$form_name}_{@name}" value="{@value}"/>
							</xsl:for-each>
						</xsl:when>

						<!-- input type="hidden" -->
						<xsl:when test="name()='input' and @type='hidden'">
							<input type="hidden" name="{$form_name}_{@name}" value="{@value}"/>
						</xsl:when>

						<!-- input type="text" -->
						<xsl:when test="name()='input'">
							<div id="{@name}" class="input_container">
								<!-- text label -->
								<div id="{@name}_label" class="input_label">
									<xsl:copy-of select="$required"/>
									<xsl:variable name="name" select="@name"/>
									<xsl:value-of select="$lang_form/*[name()=$name]"/>
								</div>
								<!-- form element -->
								<div id="{@name}_input" class="input_field">
									<xsl:variable name="name" select="@name"/>
									<input name="{$form_name}_{@name}" id="{@name}_field">
										<xsl:attribute name="type">
											<xsl:choose>
												<xsl:when test="@type='text'">
													<xsl:text>text</xsl:text>
												</xsl:when>
												<xsl:when test="@type='file'">
													<xsl:text>file</xsl:text>
												</xsl:when>
												<xsl:otherwise>
													<xsl:text>text</xsl:text>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:attribute>
										<xsl:if test="@value">
											<xsl:attribute name="value"><xsl:value-of select="@value"/></xsl:attribute>
										</xsl:if>
										<xsl:if test="@maxlength">
											<xsl:attribute name="maxlength"><xsl:value-of select="@maxlength"/></xsl:attribute>
										</xsl:if>
									</input>
								</div>
								<!-- text description -->
								<xsl:variable name="descname" select="concat(@name,'_desc')"/>
								<xsl:variable name="desc" select="$lang_form/*[name()=$descname]"/>
								<xsl:if test="$desc">
									<div id="{@name}_desc">
										<xsl:value-of select="$desc"/>
									</div>
								</xsl:if>
							</div>
						</xsl:when>


						<!-- input type="radio" -->
						<xsl:when test="name()='radio'">
							<xsl:variable name="name" select="@name"/>

							<div id="{$name}" class="radio_layout_container">
								<xsl:variable name="radio_text" select="$lang_form/*[name()=$name]"/>
								<xsl:if test="$radio_text">
									<div id="{$name}_layout_label" class="radio_layout_label">
										<xsl:copy-of select="$required"/>
										<xsl:value-of select="$radio_text"/>
									</div>
								</xsl:if>
								<xsl:variable name="radio_value" select="@value"/>

								<div id="{@name}_radios" class="radio_fields_container">
									<xsl:for-each select="option">
										<div id="{$name}_{@value}_container" class="radio_container">
											<!-- form element -->
											<div id="{$name}_{@value}_radio" class="radio_field">
												<input type="radio" name="{$form_name}_{$name}" id="{$name}" value="{@value}">
													<xsl:choose>
														<xsl:when test="not($radio_value)">
																<xsl:if test="@default">
																<xsl:attribute name="checked">checked</xsl:attribute>
															</xsl:if>
														</xsl:when>
														<xsl:when test="@value=$radio_value">
															<xsl:attribute name="checked">checked</xsl:attribute>
														</xsl:when>
													</xsl:choose>
												</input>
											</div>
											<!-- text label -->
											<div id="{$name}_{@value}_label" class="radio_label">
												<xsl:variable name="option_name" select="concat($name,'_',@value)"/>
												<xsl:value-of select="$lang_form/*[name()=$option_name]"/>
											</div>
										</div>
									</xsl:for-each>
								</div>
							</div>
						</xsl:when>

						<!-- input type="submit" -->
						<xsl:when test="name()='submit'">
							<div id="{@name}" class="submit_field">
								<input type="submit" name="{$form_name}_{@name}" id="{@name}_button">
									<xsl:attribute name="value">
										<xsl:variable name="select_name" select="@name"/>
										<xsl:value-of select="$lang_form/*[name()=$select_name]"/>
									</xsl:attribute>
								</input>
							</div>
						</xsl:when>

						<!-- select -->
						<xsl:when test="name()='select'">
							<div id="{@name}" class="select_container">
								<!-- text label -->
								<xsl:variable name="select_label" select="@name"/>
								<div id="{@name}_label" class="select_label">
									<xsl:copy-of select="$required"/>
									<xsl:value-of select="$lang_form/*[name()=$select_label]"/>
								</div>
								<!-- form element -->
								<div id="{@name}_field" class="select_field">
									<select id="{@name}_select">
										<xsl:variable name="select_name" select="@name"/>
										<xsl:variable name="select_value" select="@value"/>
										<xsl:choose>
											<xsl:when test="@multiple">
												<xsl:attribute name="multiple">multiple</xsl:attribute>
												<xsl:attribute name="name"><xsl:value-of select="$form_name"/>_<xsl:value-of select="@name"/>[]</xsl:attribute>
											</xsl:when>
											<xsl:otherwise>
												<xsl:attribute name="name"><xsl:value-of select="$form_name"/>_<xsl:value-of select="@name"/></xsl:attribute>
											</xsl:otherwise>
										</xsl:choose>
										<xsl:if test="@onupdate">
											<xsl:attribute name="onUpdate" select="@onupdate"/>
										</xsl:if>
										<xsl:choose>
											<xsl:when test="@source">
												<xsl:variable name="xpathstr" select="concat(@source,'/option')"/>
												<xsl:variable name="value" select="@value"/>
												<xsl:for-each select="dyn:evaluate($xpathstr)">
													<option value="{@value}">
														<xsl:if test="$value=@value">
															<xsl:attribute name="selected">selected</xsl:attribute>
														</xsl:if>
														<xsl:value-of select="@text"/>
													</option>
												</xsl:for-each>
												<xsl:value-of select="@value"/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:for-each select="option">
													<option value="{@value}">
														<xsl:variable name="opt_value" select="@value"/>
														<xsl:choose>
															<xsl:when test="../value[@key=$opt_value]">
																<xsl:attribute name="selected">selected</xsl:attribute>
															</xsl:when>
															<xsl:when test="@value=$select_value">
																<xsl:attribute name="selected">selected</xsl:attribute>
															</xsl:when>
														</xsl:choose>
														<xsl:choose>
															<xsl:when test="@text">
																<xsl:value-of select="@text"/>
															</xsl:when>
															<xsl:otherwise>
																<xsl:variable name="option_name" select="concat($select_name,'_',@value)"/>
																<xsl:value-of select="$lang_form/*[name()=$option_name]"/>
															</xsl:otherwise>
														</xsl:choose>
													</option>
												</xsl:for-each>
											</xsl:otherwise>
										</xsl:choose>
									</select>
								</div>
								<!-- text description -->
								<xsl:variable name="descname" select="concat(@name,'_desc')"/>
								<xsl:variable name="desc" select="$lang_form/*[name()=$descname]"/>
								<xsl:if test="$desc">
									<div id="{@name}_description" class="select_description">
										<xsl:value-of select="$desc"/>
									</div>
								</xsl:if>
							</div>
						</xsl:when>

						<!-- textarea -->
						<xsl:when test="name()='textarea'">
							<xsl:variable name="name" select="@name"/>
							<div id="{@name}" class="textarea_conatiner">
								<!-- text label -->
								<div id="{@name}_text" class="textarea_label">
									<xsl:copy-of select="$required"/>
									<xsl:variable name="textarea_name" select="@name"/>
									<xsl:value-of select="$lang_form/*[name()=$textarea_name]"/>
								</div>
								<!-- form element -->
								<div id="{@name}_textarea" class="textarea_field">
									<textarea id="{@name}_field" name="{$form_name}_{@name}">
										<xsl:if test="@noeditor">
											<xsl:attribute name="noeditor">1</xsl:attribute>
										</xsl:if>
										<xsl:value-of select="@value"/>
									</textarea>
								</div>
								<!-- text descrition -->
								<xsl:variable name="descname" select="concat(@name,'_desc')"/>
								<xsl:variable name="desc" select="$lang_form/*[name()=$descname]"/>
								<xsl:if test="$desc">
									<div id="{@name}_desc" class="textarea_description">
										<xsl:value-of select="$desc"/>
									</div>
								</xsl:if>
							</div>
						</xsl:when>

						<!-- checkbox -->
						<xsl:when test="name()='checkbox'">
							<div id="{@name}" class="checkbox_container">
								<!-- form element -->
								<div id="{@name}_checkbox" class="checkbox_field">
									<input type="hidden" name="{$form_name}_{@name}" value="0"/>
									<input type="checkbox" name="{$form_name}_{@name}" id="{$form_name}_{@name}" value="1">
										<xsl:if test="@value=1">
											<xsl:attribute name="checked">checked</xsl:attribute>
										</xsl:if>
									</input>
								</div>
								<!-- text label -->
								<div id="{@name}_text" class="checkbox_label">
									<xsl:copy-of select="$required"/>
									<xsl:variable name="textarea_name" select="@name"/>
									<xsl:value-of select="$lang_form/*[name()=$textarea_name]"/>
								</div>
							</div>
						</xsl:when>

					</xsl:choose>
				</xsl:for-each>

				<input type="hidden" name="{$form_name}_submit" value="1"/>

			</form>
		</div>
	</xsl:template>
</xsl:stylesheet>
