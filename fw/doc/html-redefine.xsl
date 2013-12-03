<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


	<xsl:template name="html-body">
		<body>
			<!-- ... Обвязка страницы ... -->
			<xsl:call-template name="content-wrapper"/>
			<!-- ... Обвязка страницы ... -->
		</body>
	</xsl:template>


	<xsl:template name="content">

		<!-- Вызов автоматически рендерящихся шаблонов -->
		<xsl:apply-templates select="/root/content/*"/>

		<!-- Обновляемые куски страниц с классом .switcher и уникальными id -->
		<div id="id1" class="switcher">
			<!-- обновляемая часть 1 -->
		</div>
		<div id="id2" class="switcher">
			<!-- обновляемая часть 2 -->
		</div>
		<div id="id3" class="switcher">
			<!-- обновляемая часть 3 -->
		</div>
		<!-- ... -->

	</xsl:template>


</xsl:stylesheet>