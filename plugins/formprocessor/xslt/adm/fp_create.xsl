<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template match="FP_create">

        <h2>
            <xsl:value-of select="$locale/formProcessor/adm/title"/>
            <xsl:text> → </xsl:text>
            <a href="/adm/formprocessor/manage">
                <xsl:value-of select="$locale/formProcessor/adm/manage/title"/>
            </a>
            <xsl:text> → </xsl:text>
            <xsl:value-of select="$locale/formProcessor/adm/create/title"/>
        </h2>

        <form class="ajaxer" action="/adm/formprocessor/saveform" method="post">

            <h3>
                <a href="#" class="action down turner" id="mainFormParams-turner" onclick="formProcessor.turndown( 'mainFormParams' );" />
                <xsl:value-of select="$locale/formProcessor/adm/create/mainData"/>
            </h3>

            <div id="mainFormParams">
                <table class="form">
                    <tr>
                        <th><xsl:value-of select="$locale/formProcessor/adm/create/name"/></th>
                        <td><input type="text" name="name" /></td>
                    </tr>
                    <tr>
                        <th><xsl:value-of select="$locale/formProcessor/adm/create/uri"/></th>
                        <td>
                            <div class="container">
                                <input type="text" name="uri"/>
                                <div class="invalid">
                                    <div class="invalid-text"/>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><xsl:value-of select="$locale/formProcessor/adm/create/notify"/></th>
                        <td><input type="text" name="notify"/></td>
                    </tr>
                    <tr>
                        <th><xsl:value-of select="$locale/formProcessor/adm/create/buttonNotify"/></th>
                        <td><input type="text" name="button"/></td>
                    </tr>
                </table>
                <h3><xsl:value-of select="$locale/formProcessor/adm/create/description"/></h3>
                <textarea name="description" editor="Full" cols="" rows=""></textarea>
            </div>

            <h3>
                <a href="#" class="action down turner" id="formFieldsParams-turner" onclick="formProcessor.turndown( 'formFieldsParams' );"/>
                <xsl:value-of select="$locale/formProcessor/adm/create/generator"/></h3>

            <div id="formFieldsParams">
                <table class="formGenerator">
                    <tr>
                        <td>
                            <h3><xsl:value-of select="$locale/formProcessor/adm/create/fields"/></h3>
                            <div id="formFields">

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

            <input type="submit" value="{$locale/formProcessor/adm/create/addForm}" />

        </form>

        <xsl:call-template name="FP_fieldTypes"/>

    </xsl:template>
</xsl:stylesheet>
