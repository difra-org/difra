<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
	<xsl:template match="/root/GalleryConfig">
		<h2>
			<xsl:value-of select="$locale/gallery/adm/config/title"/>
		</h2>
		<form action="/adm/gallery/config/save" class="ajaxer">
			<h3>
				<xsl:value-of select="$locale/gallery/adm/config/title-secondary"/>
			</h3>
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/gallery/adm/config/perpage"/>
					</th>
					<td>
						<input type="number" name="perpage" value="{@perpage}"/>
					</td>
				</tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/gallery/adm/config/format"/>
                    </th>
                    <td>
                        <select name="format">
                            <option value="png">
                                <xsl:text>png</xsl:text>
                            </option>
                            <option value="jpg">
                                <xsl:if test="@format='jpg'">
                                    <xsl:attribute name="selected">
                                        <xsl:text>selected</xsl:text>
                                    </xsl:attribute>
                                </xsl:if>
                                <xsl:text>jpg</xsl:text>
                            </option>
                        </select>
                    </td>
                </tr>
			</table>
			<h3>
				<xsl:value-of select="$locale/gallery/adm/config/title-images"/>
			</h3>
			<table class="form">
				<tr>
					<th>
						<xsl:value-of select="$locale/gallery/adm/config/image-sizes" disable-output-escaping="yes"/>
					</th>
					<td>
						<textarea rows="5" cols="25" name="imgSizes">
							<xsl:value-of select="@imgSizes"/>
						</textarea>
						<div class="small gray">
							<xsl:value-of select="$locale/gallery/adm/config/image-sizes-info" disable-output-escaping="yes"/>
						</div>
					</td>
				</tr>
			</table>

            <h3>
                <xsl:value-of select="$locale/gallery/adm/config/waterMarkSettings"/>
            </h3>

            <table class="form">
                <tr>
                    <th>
                        <xsl:value-of select="$locale/gallery/adm/config/waterMarkOnOff"/>
                    </th>
                    <td>
                        <input type="checkbox" name="waterOn">
                            <xsl:if test="@waterOn=1">
                                <xsl:attribute name="checked">
                                    <xsl:text>checked</xsl:text>
                                </xsl:attribute>
                            </xsl:if>
                        </input>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/gallery/adm/config/waterOnPreview"/>
                    </th>
                    <td>
                        <input type="checkbox" name="waterPreviewOn">
                            <xsl:if test="@waterOnPreview=1">
                                <xsl:attribute name="checked">
                                    <xsl:text>checked</xsl:text>
                                </xsl:attribute>
                            </xsl:if>
                        </input>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/gallery/adm/config/waterText"/>
                    </th>
                    <td>
                        <input type="text" name="waterText" value="{@waterText}"/>
                    </td>
                </tr>
                <tr>
                    <th>
                        <xsl:value-of select="$locale/gallery/adm/config/waterPicture"/>
                        <xsl:if test="@waterFile=1">
                            <br/>
                            <img src="/gallery/watermark.png"/>
                        </xsl:if>
                    </th>
                    <td>
                        <input type="file" name="waterFile"/>
                    </td>
                </tr>
            </table>

			<input type="submit" value="{$locale/adm/save}"/>
		</form>
	</xsl:template>
</xsl:stylesheet>