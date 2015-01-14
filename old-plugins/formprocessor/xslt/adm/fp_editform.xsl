<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="FP_editform">

        <h2>
            <xsl:value-of select="$locale/formProcessor/adm/title"/>
            <xsl:text> → </xsl:text>
            <a href="/adm/formprocessor/manage">
                <xsl:value-of select="$locale/formProcessor/adm/manage/title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/formProcessor/adm/edit/title"/>
        </h2>

        <form class="ajaxer" action="/adm/formprocessor/saveform" method="post">

            <input type="hidden" name="formId" value="{form/@id}" />
            <input type="hidden" name="originalUri" value="{form/@uri}"/>

            <h3>
                <a href="#" class="action down turner" id="mainFormParams-turner" onclick="formProcessor.turndown( 'mainFormParams' );"/>
                <xsl:value-of select="$locale/formProcessor/adm/create/mainData"/>
            </h3>

            <div id="mainFormParams">
                <table class="form">
                    <tr>
                        <th>
                            <xsl:value-of select="$locale/formProcessor/adm/create/name"/>
                        </th>
                        <td>
                            <input type="text" name="name" value="{form/@title}"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <xsl:value-of select="$locale/formProcessor/adm/create/uri"/>
                        </th>
                        <td>
                            <div class="container">
                                <input type="text" name="uri" value="{form/@uri}" />
                                <div class="invalid">
                                    <div class="invalid-text"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <xsl:value-of select="$locale/formProcessor/adm/create/notify"/>
                        </th>
                        <td>
                            <input type="text" name="notify" value="{form/@answer}"/>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <xsl:value-of select="$locale/formProcessor/adm/create/buttonNotify"/>
                        </th>
                        <td>
                            <input type="text" name="button" value="{form/@submit}"/>
                        </td>
                    </tr>
                </table>
                <h3>
                    <xsl:value-of select="$locale/formProcessor/adm/create/description"/>
                </h3>
                <textarea name="description" editor="Full" cols="" rows="">
                    <xsl:value-of select="form/@description" disable-output-escaping="yes"/>
                </textarea>
            </div>

            <h3>
                <a href="#" class="action down turner" id="formFieldsParams-turner" onclick="formProcessor.turndown( 'formFieldsParams' );"/>
                <xsl:value-of select="$locale/formProcessor/adm/create/generator"/>
            </h3>

            <div id="formFieldsParams">
                <table class="formGenerator">
                    <tr>
                        <td>
                            <h3>
                                <xsl:value-of select="$locale/formProcessor/adm/create/fields"/>
                            </h3>
                            <div id="formFields">
                                
                                <xsl:for-each select="form/fields/field">

                                    <xsl:variable name="typeText" select="@type"/>

                                    <div class="formField type-{@type}" id="addedField-{position()}">

                                        <div class="fieldTypeTitle">
                                            <xsl:value-of select="$locale/formProcessor/adm/formtypes/*[name()=$typeText]/text()"/>
                                        </div>
                                        <div class="controlls">
                                            <a href="#" class="action up" onclick="formProcessor.up( this );"/>
                                            <a href="#" class="action down" onclick="formProcessor.down( this );"/>
                                            <a href="#" class="action delete" onclick="formProcessor.delete( this );"/>
                                        </div>
                                        <div style="clear: both;"/>

                                        <input type="hidden" value="{@type}" name="fieldType[]"/>

                                        <!-- поля -->

                                        <label class="fieldLabel">
                                            <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                                        </label>
                                        <input type="text" class="basicField fieldLabel" name="fieldName[]" value="{@name}"/>
                                        <label class="fieldLabel">
                                            <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                                        </label>
                                        <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows="">
                                            <xsl:value-of select="@description"/>
                                        </textarea>

                                        <xsl:if test="@type='select' or @type='radio'">

                                            <div class="division">
                                                <label class="fieldLabel">
                                                    <xsl:value-of select="$locale/formProcessor/adm/create/variants"/>
                                                </label>
                                                <span class="small gray">
                                                    <xsl:value-of select="$locale/formProcessor/adm/create/variantsInputDesc"/>
                                                </span>
                                                <textarea name="selectVariants[]" class="basicField selectVariants" cols="" rows="">
                                                    <xsl:for-each select="variants/variant">
                                                        <xsl:value-of select="@value"/>
                                                        <xsl:if test="not(position()=last())">
                                                            <xsl:text>;</xsl:text>
                                                        </xsl:if>
                                                    </xsl:for-each>
                                                </textarea>
                                            </div>

                                        </xsl:if>

                                        <div class="division">
                                            <label>
                                                <input type="checkbox" name="fieldMandatory[]" value="1">
                                                    <xsl:if test="@mandatory=1">
                                                        <xsl:attribute name="checked">
                                                            <xsl:text>checked</xsl:text>
                                                        </xsl:attribute>
                                                    </xsl:if>
                                                </input>
                                                <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                                            </label>
                                        </div>

                                    </div>
                                </xsl:for-each>
                                
                            </div>
                            <div id="addField">
                                <select id="fieldType">
                                    <option value="0" disabled="disabled" selected="selected">
                                        <xsl:value-of select="$locale/formProcessor/adm/create/fieldType"/>
                                    </option>
                                    <xsl:for-each select="$locale/formProcessor/adm/formtypes/*">
                                        <option value="{name()}">
                                            <xsl:value-of select="text()"/>
                                        </option>
                                    </xsl:for-each>
                                </select>
                                <a href="#" class="action add" onclick="formProcessor.createField();"/>
                            </div>
                        </td>
                        <td>
                            <h3>
                                <xsl:value-of select="$locale/formProcessor/adm/create/preview"/>
                                <a href="#" class="action view" title="{$locale/formProcessor/adm/create/refresh}" onclick="formProcessor.makePreview();">
                                    <xsl:value-of select="$locale/formProcessor/adm/create/refresh"/>
                                </a>
                            </h3>
                            <div id="formPreview">
                                <table>

                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <input type="submit" value="{$locale/formProcessor/adm/edit/save}"/>

            <script type="text/javascript">
                formProcessor.setFieldsCount( <xsl:value-of select="count(form/fields/field)"/> );
            </script>

        </form>

        <xsl:call-template name="FP_fieldTypes"/>

    </xsl:template>
</xsl:stylesheet>
