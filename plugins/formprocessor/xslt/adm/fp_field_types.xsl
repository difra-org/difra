<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">

    <xsl:template name="FP_fieldTypes">

        <div id="formTypes" class="noDisplay">

            <div class="formField type-text">

                <!-- text -->

                <div class="fieldTypeTitle">
                    <xsl:value-of select="$locale/formProcessor/adm/formtypes/text"/>
                </div>
                <div class="controlls">
                    <a href="#" class="action up" onclick="formProcessor.up( this );" />
                    <a href="#" class="action down" onclick="formProcessor.down( this );" />
                    <a href="#" class="action delete" onclick="formProcessor.delete( this );" />
                </div>
                <div style="clear: both;"/>

                <input type="hidden" value="text" name="fieldType[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                </label>
                <input type="text" class="basicField fieldLabel" name="fieldName[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                </label>
                <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows="" />

                <div class="division">
                    <label>
                        <input type="checkbox" name="fieldMandatory[]" value="1"/>
                        <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                    </label>
                </div>

            </div>

            <!-- textarea -->

            <div class="formField type-textarea">

                <div class="fieldTypeTitle">
                    <xsl:value-of select="$locale/formProcessor/adm/formtypes/textarea"/>
                </div>
                <div class="controlls">
                    <a href="#" class="action up" onclick="formProcessor.up( this );" />
                    <a href="#" class="action down" onclick="formProcessor.down( this );" />
                    <a href="#" class="action delete" onclick="formProcessor.delete( this );" />
                </div>
                <div style="clear: both;"/>

                <input type="hidden" value="textarea" name="fieldType[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                </label>
                <input type="text" class="basicField fieldLabel" name="fieldName[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                </label>
                <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows="" />

                <div class="division">
                    <label>
                        <input type="checkbox" name="fieldMandatory[]" value="1"/>
                        <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                    </label>
                </div>

            </div>

            <!-- numeric -->

            <div class="formField type-numeric">

                <div class="fieldTypeTitle">
                    <xsl:value-of select="$locale/formProcessor/adm/formtypes/numeric"/>
                </div>
                <div class="controlls">
                    <a href="#" class="action up" onclick="formProcessor.up( this );"/>
                    <a href="#" class="action down" onclick="formProcessor.down( this );"/>
                    <a href="#" class="action delete" onclick="formProcessor.delete( this );"/>
                </div>
                <div style="clear: both;"/>

                <input type="hidden" value="numeric" name="fieldType[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                </label>
                <input type="text" class="basicField fieldLabel" name="fieldName[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                </label>
                <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows="" />

                <div class="division">
                    <label>
                        <input type="checkbox" name="fieldMandatory[]" value="1"/>
                        <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                    </label>
                </div>

            </div>

            <!-- checkbox -->

            <div class="formField type-checkbox">

                <div class="fieldTypeTitle">
                    <xsl:value-of select="$locale/formProcessor/adm/formtypes/checkbox"/>
                </div>
                <div class="controlls">
                    <a href="#" class="action up" onclick="formProcessor.up( this );"/>
                    <a href="#" class="action down" onclick="formProcessor.down( this );"/>
                    <a href="#" class="action delete" onclick="formProcessor.delete( this );"/>
                </div>
                <div style="clear: both;"/>

                <input type="hidden" value="checkbox" name="fieldType[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                </label>
                <input type="text" class="basicField fieldLabel" name="fieldName[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                </label>
                <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows="" />

                <div class="division">
                    <label>
                        <input type="checkbox" name="fieldMandatory[]" value="1"/>
                        <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                    </label>
                </div>

            </div>

            <!-- select -->

            <div class="formField type-select">

                <div class="fieldTypeTitle">
                    <xsl:value-of select="$locale/formProcessor/adm/formtypes/select"/>
                </div>
                <div class="controlls">
                    <a href="#" class="action up" onclick="formProcessor.up( this );"/>
                    <a href="#" class="action down" onclick="formProcessor.down( this );"/>
                    <a href="#" class="action delete" onclick="formProcessor.delete( this );"/>
                </div>
                <div style="clear: both;"/>

                <input type="hidden" value="select" name="fieldType[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                </label>
                <input type="text" class="basicField fieldLabel" name="fieldName[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                </label>
                <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows="" />

                <div class="division">
                    <label class="fieldLabel">
                        <xsl:value-of select="$locale/formProcessor/adm/create/variants"/>
                    </label>
                    <span class="small gray">
                        <xsl:value-of select="$locale/formProcessor/adm/create/variantsInputDesc"/>
                    </span>
                    <textarea name="selectVariants[]" class="basicField selectVariants" cols="" rows=""/>
                </div>

                <div class="division">
                    <label>
                        <input type="checkbox" name="fieldMandatory[]" value="1"/>
                        <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                    </label>
                </div>
            </div>

            <!-- radio -->

            <div class="formField type-radio">

                <div class="fieldTypeTitle">
                    <xsl:value-of select="$locale/formProcessor/adm/formtypes/radio"/>
                </div>
                <div class="controlls">
                    <a href="#" class="action up" onclick="formProcessor.up( this );"/>
                    <a href="#" class="action down" onclick="formProcessor.down( this );"/>
                    <a href="#" class="action delete" onclick="formProcessor.delete( this );"/>
                </div>
                <div style="clear: both;"/>

                <input type="hidden" value="radio" name="fieldType[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldName"/>
                </label>
                <input type="text" class="basicField fieldLabel" name="fieldName[]"/>
                <label class="fieldLabel">
                    <xsl:value-of select="$locale/formProcessor/adm/create/fieldDescription"/>
                </label>
                <textarea name="fieldDescription[]" class="basicField fieldDescription" cols="" rows=""/>

                <div class="division">
                    <label class="fieldLabel">
                        <xsl:value-of select="$locale/formProcessor/adm/create/variants"/>
                    </label>
                    <span class="small gray">
                        <xsl:value-of select="$locale/formProcessor/adm/create/variantsInputDesc"/>
                    </span>
                    <textarea name="selectVariants[]" class="basicField selectVariants" cols="" rows=""/>
                </div>

                <div class="division">
                    <label>
                        <input type="checkbox" name="fieldMandatory[]" value="1"/>
                        <xsl:value-of select="$locale/formProcessor/adm/create/mandatory"/>
                    </label>
                </div>
            </div>

        </div>

    </xsl:template>
</xsl:stylesheet>