<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:template match="paginator">
        <xsl:call-template name="paginator">
            <xsl:with-param name="current" select="@page"/>
            <xsl:with-param name="pages" select="@pages"/>
            <xsl:with-param name="link" select="@link"/>
            <xsl:with-param name="get" select="@get"/>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="paginator">
        <xsl:param name="i" select="1"/>
        <xsl:param name="pages"/>
        <xsl:param name="link"/>
        <xsl:param name="current"/>
        <xsl:param name="get"/>
        <div class="paginator">
            <xsl:call-template name="paginatorSub">
                <xsl:with-param name="i" select="$i"/>
                <xsl:with-param name="pages" select="$pages"/>
                <xsl:with-param name="link" select="$link"/>
                <xsl:with-param name="current" select="$current"/>
                <xsl:with-param name="get" select="$get"/>
            </xsl:call-template>
        </div>
    </xsl:template>

    <xsl:template name="paginatorSub">
        <xsl:param name="i" select="1"/>
        <xsl:param name="pages"/>
        <xsl:param name="link"/>
        <xsl:param name="current"/>
        <xsl:param name="get"/>

        <xsl:variable name="prevPages" select="4"/>
        <xsl:variable name="nextPages" select="4"/>

        <xsl:text> </xsl:text>

        <xsl:if test="$pages &gt; 1 or $current != 1">
            <!-- кнопка назад -->
            <xsl:if test="$i = 1">
                <xsl:variable name="disabled">
                    <xsl:choose>
                        <xsl:when test="$current = 1">1</xsl:when>
                        <xsl:otherwise>0</xsl:otherwise>
                    </xsl:choose>
                </xsl:variable>
                <xsl:call-template name="paginatorLink">
                    <xsl:with-param name="get" select="$get"/>
                    <xsl:with-param name="link" select="$link"/>
                    <xsl:with-param name="page" select="$current - 1"/>
                    <xsl:with-param name="text">←</xsl:with-param>
                    <xsl:with-param name="disabled" select="$disabled"/>
                    <xsl:with-param name="addClasses" select="'pagerPrev'"/>
                </xsl:call-template>
                <xsl:text> </xsl:text>
            </xsl:if>

            <xsl:choose>
                <xsl:when test="$i = $current">
                    <xsl:call-template name="paginatorLink">
                        <xsl:with-param name="get" select="$get"/>
                        <xsl:with-param name="link" select="'#'"/>
                        <xsl:with-param name="page" select="$i"/>
                        <xsl:with-param name="text" select="$i"/>
                        <xsl:with-param name="disabled" select="1"/>
                    </xsl:call-template>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:call-template name="paginatorLink">
                        <xsl:with-param name="get" select="$get"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="page" select="$i"/>
                        <xsl:with-param name="text" select="$i"/>
                    </xsl:call-template>
                </xsl:otherwise>
            </xsl:choose>

            <xsl:choose>
                <!-- скипаем от первого в середину -->
                <xsl:when test="$i &lt; $current - $prevPages and $current - $prevPages &gt; 3">
                    <xsl:call-template name="paginatorLink">
                        <xsl:with-param name="get" select="$get"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="page" select="$i"/>
                        <xsl:with-param name="text" select="'...'"/>
                        <xsl:with-param name="disabled" select="1"/>
                        <xsl:with-param name="addClasses" select="'pagerSkip'"/>
                        <xsl:with-param name="notButton" select="1"/>
                    </xsl:call-template>
                    <xsl:call-template name="paginatorSub">
                        <xsl:with-param name="pages" select="$pages"/>
                        <xsl:with-param name="current" select="$current"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="i" select="$current - 4"/>
                        <xsl:with-param name="get" select="$get"/>
                    </xsl:call-template>
                </xsl:when>

                <!-- скипаем от средних до последних -->
                <xsl:when test="$i &gt; $current + $nextPages and $i &lt; $pages - 2">
                    <xsl:call-template name="paginatorLink">
                        <xsl:with-param name="get" select="$get"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="page" select="$i"/>
                        <xsl:with-param name="text" select="'...'"/>
                        <xsl:with-param name="disabled" select="1"/>
                        <xsl:with-param name="addClasses" select="'pagerSkip'"/>
                        <xsl:with-param name="notButton" select="1"/>
                    </xsl:call-template>
                    <xsl:call-template name="paginatorSub">
                        <xsl:with-param name="pages" select="$pages"/>
                        <xsl:with-param name="current" select="$current"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="i" select="$pages"/>
                        <xsl:with-param name="get" select="$get"/>
                    </xsl:call-template>
                </xsl:when>

                <!-- показываемые страницы -->
                <xsl:when test="$i &lt; $pages">
                    <xsl:call-template name="paginatorSub">
                        <xsl:with-param name="pages" select="$pages"/>
                        <xsl:with-param name="current" select="$current"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="i" select="$i + 1"/>
                        <xsl:with-param name="get" select="$get"/>
                    </xsl:call-template>
                </xsl:when>

                <!-- кнопка вперёд -->
                <xsl:otherwise>
                    <xsl:variable name="disabled">
                        <xsl:choose>
                            <xsl:when test="$current = $pages">1</xsl:when>
                            <xsl:otherwise>0</xsl:otherwise>
                        </xsl:choose>
                    </xsl:variable>
                    <!--<xsl:if test="$current &lt; $pages">-->
                    <xsl:call-template name="paginatorLink">
                        <xsl:with-param name="get" select="$get"/>
                        <xsl:with-param name="link" select="$link"/>
                        <xsl:with-param name="page" select="$current + 1"/>
                        <xsl:with-param name="text">→</xsl:with-param>
                        <xsl:with-param name="addClasses">pagerNext</xsl:with-param>
                        <xsl:with-param name="disabled" select="$disabled"/>
                    </xsl:call-template>
                    <!--</xsl:if>-->
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:template>

    <xsl:template name="paginatorLink">
        <xsl:param name="link"/>
        <xsl:param name="page"/>
        <xsl:param name="get"/>
        <xsl:param name="text"/>
        <xsl:param name="disabled" select="0"/>
        <xsl:param name="notButton" select="0"/>
        <xsl:param name="addClasses" select="''"/>

        <xsl:text> </xsl:text>

        <a>
            <xsl:attribute name="class">
                <xsl:text>btn pagerItem</xsl:text>
                <xsl:choose>
                    <xsl:when test="$notButton=0">
                        <xsl:text> btn-outline-secondary</xsl:text>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:text> btn-link</xsl:text>
                    </xsl:otherwise>
                </xsl:choose>
                <xsl:if test="$addClasses!=''">
                    <xsl:text> </xsl:text>
                    <xsl:value-of select="$addClasses"/>
                </xsl:if>
                <xsl:if test="$disabled>0">
                    <xsl:text> disabled</xsl:text>
                </xsl:if>
            </xsl:attribute>
            <xsl:attribute name="href">
                <xsl:choose>
                    <xsl:when test="$disabled>0">
                        <xsl:text>#</xsl:text>
                    </xsl:when>
                    <xsl:when test="$page=1">
                        <xsl:value-of select="$link"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="$link"/>
                        <xsl:choose>
                            <xsl:when test="$page = 1"/>
                            <xsl:when test="not($get) or ($get='')">
                                <xsl:text>/page/</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="$get"/>
                                <xsl:text>page=</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                        <xsl:value-of select="$page"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:attribute>
            <xsl:value-of select="$text"/>
        </a>
    </xsl:template>
</xsl:stylesheet>
