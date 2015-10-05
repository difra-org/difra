<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0">
    <xsl:template name="register-form">
        <h2>
            <xsl:value-of select="$locale/auth/forms/registrationTitle"/>
        </h2>
        <div class="container">
            <input type="text"
                   name="email"
                   placeholder="{$locale/auth/placeholders/email}"/>
            <span class="status">
                <xsl:value-of select="$locale/auth/fieldDescs/email"/>
            </span>
        </div>
        <div class="container">
            <input type="password"
                   name="password1"
                   placeholder="{$locale/auth/placeholders/password}"/>
            <span class="status">
                <xsl:value-of select="$locale/auth/fieldDescs/password"/>
            </span>
        </div>
        <div class="container">
            <input type="password"
                   name="password2"
                   placeholder="{$locale/auth/placeholders/password}"/>
            <span class="status">
                <xsl:value-of select="$locale/auth/fieldDescs/password2"/>
            </span>
        </div>
        <div class="container">
            <input type="text"
                   name="nickname"
                   placeholder="{$locale/auth/placeholders/nickname}"/>
            <span class="status">
                <xsl:value-of select="$locale/auth/fieldDescs/nickname"/>
            </span>
        </div>
        <div class="container">
            <span id="capchaDiv">
                <img src="/capcha" class="capcha" id="capcha-image" alt="capcha"/>
            </span>
            <xsl:text></xsl:text>
            <span style="position:relative;left:-20px;top:-10px;margin-right:-16px;">
                <a href="#"
                   onclick="$('#capcha-image').attr('src','/capcha?t='+(new Date().getTime()))"
                   style="text-decoration:none">
                    <img src="/images/icon-reload.png" alt="refresh"/>
                </a>
            </span>
            <input type="text"
                   name="capcha"
                   style="width: 170px;"
                   placeholder="{$locale/auth/placeholders/capcha}"/>
            <span class="status">
                <xsl:value-of select="$locale/auth/fieldDescs/capcha"/>
            </span>
        </div>

        <div class="button submit">
            <xsl:value-of select="$locale/auth/forms/registration"/>
        </div>
    </xsl:template>
</xsl:stylesheet>