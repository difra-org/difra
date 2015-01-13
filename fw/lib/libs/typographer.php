<?php

//typographer

namespace Difra\Libs;

/**
 * Class Typographer
 *
 * @package Difra\Libs
 */
class Typographer {

	private $text;

	private $quot11; // Откр. кавычка первого уровня
	private $quot12; // Закр. кавычка первого уровня
	private $quot21; // Откр. кавычка второго уровня
	private $quot22; // Закр. кавычка второго уровня

	private $tire; // Тире
	private $tireinterval; // Тире в интервалах
	private $number; // Знак №
	private $sect; // Знак параграфа
	private $sup2; // Степень квадрата
	private $sup3; // Степень куба
	private $deg; // Знак градуса
	private $Prime; // Знак дюйма
	private $euro; // Знак евро
	private $times; // Знак умножения
	private $plusmn; // Плюс-минус
	private $space; // Неразрывный пробел

	private $spaceAfterShortWord = true; // Пробел после коротких слов,  true или false
	private $lengthShortWord = 2; // Длина короткого слова
	private $delTab = false; // Удаление табов, если установлено false, табы заменяются на пробелы,  true или false
	private $replaceTab = true; // Замена табов на пробелы,  true или false
	private $spaceBeforeLastWord = true; // Пробел перед последним словом,  true или false
	private $lengthLastWord = 3; // Длина последнего слова
	private $spaceAfterNum = true; // Пробел после №,  true или false

	private $spaceBeforeParticles = true; // Пробел перед частицами - ли, же, бы.  true или false
	private $delRepeatSpace = true; // Удалять повторы пробелов,  true или false
	private $delSpaceBeforePunctuation = true; // Удалять пробел перед знаками препинания,  true или false
	private $delSpaceBeforeProcent = true; // Удалять пробел перед знаком процента,  true или false

	private $doReplaceBefore = true; // Делать замену перед типографированием. true или false
	private $doReplaceAfter = true; // Делать замену после типографирования. true или false
	private $doMacros = true; // Использователь макросы при типографировании. true или false
	private $p = true;
	private $br = false;

	function __construct() {

		// установки символов
		$Locale = \Difra\Locales::getInstance();

		$this->quot11 = $Locale->getXPath( 'typographer/quot11' );
		$this->quot12 = $Locale->getXPath( 'typographer/quot12' );
		$this->quot21 = $Locale->getXPath( 'typographer/quot21' );
		$this->quot22 = $Locale->getXPath( 'typographer/quot22' );
		$this->space = $Locale->getXPath( 'typographer/space' );
		$this->tire = $Locale->getXPath( 'typographer/tire' );
		$this->tireinterval = $Locale->getXPath( 'typographer/tireinterval' );
		$this->number = $Locale->getXPath( 'typographer/number' );
		$this->hellip = $Locale->getXPath( 'typographer/hellip' );
		$this->sect = $Locale->getXPath( 'typographer/sect' );
		$this->sup2 = $Locale->getXPath( 'typographer/sup2' );
		$this->sup3 = $Locale->getXPath( 'typographer/sup3' );
		$this->deg = $Locale->getXPath( 'typographer/deg' );
		$this->euro = $Locale->getXPath( 'typographer/euro' );
		$this->cent = $Locale->getXPath( 'typographer/cent' );
		$this->pound = $Locale->getXPath( 'typographer/pound' );
		$this->Prime = $Locale->getXPath( 'typographer/Prime' );
		$this->times = $Locale->getXPath( 'typographer/times' );
		$this->plusmn = $Locale->getXPath( 'typographer/plusmn' );

		$this->darr = $Locale->getXPath( 'typographer/darr' );
		$this->uarr = $Locale->getXPath( 'typographer/uarr' );
		$this->larr = $Locale->getXPath( 'typographer/larr' );
		$this->rarr = $Locale->getXPath( 'typographer/rarr' );
		$this->crarr = $Locale->getXPath( 'typographer/crarr' );

		$this->_setSettings();
	}

	private function _setSettings() {

		$settings = \Difra\Config::getInstance()->get( 'typograph' );
		if( $settings ) {
			// установка настроек типографирования

			if( isset( $settings['spaceAfterShortWord'] ) ) {
				$this->spaceAfterShortWord = $settings['spaceAfterShortWord'];
			}
			if( isset( $settings['lengthShortWord'] ) ) {
				$this->lengthShortWord = $settings['lengthShortWord'];
			}
			if( isset( $settings['spaceBeforeLastWord'] ) ) {
				$this->spaceBeforeLastWord = $settings['spaceBeforeLastWord'];
			}
			if( isset( $settings['lengthLastWord'] ) ) {
				$this->lengthLastWord = $settings['lengthLastWord'];
			}
			if( isset( $settings['spaceAfterNum'] ) ) {
				$this->spaceAfterNum = $settings['spaceAfterNum'];
			}
			if( isset( $settings['spaceBeforeParticles'] ) ) {
				$this->spaceBeforeParticles = $settings['spaceBeforeParticles'];
			}
			if( isset( $settings['delRepeatSpace'] ) ) {
				$this->delRepeatSpace = $settings['delRepeatSpace'];
			}
			if( isset( $settings['delSpaceBeforePunctuation'] ) ) {
				$this->delSpaceBeforePunctuation = $settings['delSpaceBeforePunctuation'];
			}
			if( isset( $settings['delSpaceBeforeProcent'] ) ) {
				$this->delSpaceBeforeProcent = $settings['delSpaceBeforeProcent'];
			}
			if( isset( $settings['doReplaceBefore'] ) ) {
				$this->doReplaceBefore = $settings['doReplaceBefore'];
			}
			if( isset( $settings['doReplaceAfter'] ) ) {
				$this->doReplaceAfter = $settings['doReplaceAfter'];
			}
			if( isset( $settings['doMacros'] ) ) {
				$this->doMacros = $settings['doMacros'];
			}
		}
	}

	public static function typo( $text ) {

		if( empty( $text ) ) {
			return '';
		}

		$Typo = new self;

		$Typo->text = $text;

		$b = strpos( $Typo->text, '<' );
		$e = strpos( $Typo->text, '>' );
		if( $b !== false && $e !== false ) {
			$Typo->_isHTMLCode = true;
		} else {
			$Typo->_isHTMLCode = false;
		}

		if( $Typo->doReplaceBefore ) {
			$Typo->replaceBefore();
		}

		$Typo->spaces();
		$Typo->quotes();
		$Typo->dashes();
		$Typo->pbr();
		$Typo->replaceWindowsCodes();

		if( $Typo->doReplaceAfter ) {
			$Typo->replaceAfter();
		}

		return $Typo->text;
	}

	private function replaceBefore() {

		$before = array( '(r)', '(c)', '(tm)', '+/-' );
		$after = array( '®', '©', '™', '±' );

		$this->text = str_ireplace( $before, $after, $this->text );
	}

	private function replaceAfter() {

		// Замена +- около чисел
		$this->text = preg_replace( '/(?<=^| |\>|&nbsp;|&#160;)\+-(?=\d)/', $this->plusmn, $this->text );

		// Замена 3 точек на троеточие
		$this->text = preg_replace( '/(^|[^.])\.{3}([^.]|$)/', '$1' . $this->hellip . '$2', $this->text );

		// Градусы Цельсия
		$this->text = preg_replace( '/(\d+)( |\&\#160;|\&nbsp;)?(C|F)([\W \.,:\!\?"\]\)]|$)/',
					    '$1' .
					    $this->space . $this->deg . '$3$4',
					    $this->text );

		// XXXX г.
		$this->text = preg_replace( '/(^|\D)(\d{4})г( |\.|$)/', '$1$2' . $this->space . 'г$3', $this->text );

		$m = '(км|м|дм|см|мм)';
		// Кв. км м дм см мм
		$this->text = preg_replace( '/(^|\D)(\d+)( |\&\#160;|\&nbsp;)?' . $m . '2(\D|$)/',
					    '$1$2' .
					    $this->space . '$4' . $this->sup2 . '$5',
					    $this->text );

		// Куб. км м дм см мм
		$this->text = preg_replace( '/(^|\D)(\d+)( |\&\#160;|\&nbsp;)?' . $m . '3(\D|$)/',
					    '$1$2' .
					    $this->space . '$4' . $this->sup3 . '$5',
					    $this->text );

		if( $this->doMacros ) {
			// ГРАД(n)
			$this->text = preg_replace( '/ГРАД\(([\d\.,]+?)\)/', '$1' . $this->deg, $this->text );

			// ДЮЙМ(n)
			$this->text = preg_replace( '/ДЮЙМ\(([\d\.,]+?)\)/', '$1' . $this->Prime, $this->text );

			// Замена икса в числах на знак умножения
			$this->text = preg_replace( '/(?<=\d) ?x ?(?=\d)/', $this->times, $this->text );

			// Знак евро
			$this->text = str_replace( 'ЕВРО()', $this->euro, $this->text );

			// Знак фунта
			$this->text = str_replace( 'ФУНТ()', $this->pound, $this->text );

			// Знак цента
			$this->text = str_replace( 'ЦЕНТ()', $this->cent, $this->text );

			// Стрелка вверх
			$this->text = str_replace( 'СТРВ()', $this->uarr, $this->text );

			// Стрелка вниз
			$this->text = str_replace( 'СТРН()', $this->darr, $this->text );

			// Стрелка влево
			$this->text = str_replace( 'СТРЛ()', $this->larr, $this->text );

			// Стрелка вправо
			$this->text = str_replace( 'СТРП()', $this->rarr, $this->text );

			// Стрелка ввод
			$this->text = str_replace( 'ВВОД()', $this->crarr, $this->text );
		}
	}

	private function quotes() {

		$quotes = array( '&quot;', '&laquo;', '&raquo;', '«', '»', '&#171;', '&#187;', '&#147;', '&#132;', '&#8222;', '&#8220;' );
		$this->text = str_replace( $quotes, '"', $this->text );

		$this->text = preg_replace( '/([^=]|\A)""(\.{2,4}[а-яА-Я\w\-]+|[а-яА-Я\w\-]+)/', '$1<typo:quot1>"$2', $this->text );
		$this->text = preg_replace( '/([^=]|\A)"(\.{2,4}[а-яА-Я\w\-]+|[а-яА-Я\w\-]+)/', '$1<typo:quot1>$2', $this->text );

		$this->text = preg_replace( '/([а-яА-Я\w\.\-]+)""([\n\.\?\!, \)][^>]{0,1})/', '$1"</typo:quot1>$2', $this->text );
		$this->text = preg_replace( '/([а-яА-Я\w\.\-]+)"([\n\.\?\!, \)][^>]{0,1})/', '$1</typo:quot1>$2', $this->text );

		$this->text = preg_replace( '/(<\/typo:quot1>[\.\?\!]{1,3})"([\n\.\?\!, \)][^>]{0,1})/', '$1</typo:quot1>$2', $this->text );
		$this->text = preg_replace( '/(<typo:quot1>[а-яА-Я\w\.\- \n]*?)<typo:quot1>(.+?)<\/typo:quot1>/',
					    '$1<typo:quot2>$2</typo:quot2>',
					    $this->text );
		$this->text = preg_replace( '/(<\/typo:quot2>.+?)<typo:quot1>(.+?)<\/typo:quot1>/', '$1<typo:quot2>$2</typo:quot2>', $this->text );
		$this->text = preg_replace( '/(<typo:quot2>.+?<\/typo:quot2>)\.(.+?<typo:quot1>)/', '$1<\/typo:quot1>.$2', $this->text );
		$this->text = preg_replace( '/(<typo:quot2>.+?<\/typo:quot2>)\.(?!<\/typo:quot1>)/', '$1</typo:quot1>.$2$3$4', $this->text );
		$this->text = preg_replace( '/""/', '</typo:quot2></typo:quot1>', $this->text );
		$this->text = preg_replace( '/(?<=<typo:quot2>)(.+?)<typo:quot1>(.+?)(?!<\/typo:quot2>)/', '$1<typo:quot2>$2', $this->text );
		$this->text = preg_replace( '/"/', '</typo:quot1>', $this->text );

		$this->text = preg_replace( '/(<[^>]+)<\/typo:quot\d>/', '$1"', $this->text );
		$this->text = preg_replace( '/(<[^>]+)<\/typo:quot\d>/', '$1"', $this->text );
		$this->text = preg_replace( '/(<[^>]+)<\/typo:quot\d>/', '$1"', $this->text );
		$this->text = preg_replace( '/(<[^>]+)<\/typo:quot\d>/', '$1"', $this->text );
		$this->text = preg_replace( '/(<[^>]+)<\/typo:quot\d>/', '$1"', $this->text );
		$this->text = preg_replace( '/(<[^>]+)<\/typo:quot\d>/', '$1"', $this->text );

		$this->text = str_replace( '<typo:quot1>', $this->quot11, $this->text );
		$this->text = str_replace( '</typo:quot1>', $this->quot12, $this->text );
		$this->text = str_replace( '<typo:quot2>', $this->quot21, $this->text );
		$this->text = str_replace( '</typo:quot2>', $this->quot22, $this->text );
	}

	private function dashes() {

		$tires = array( '&mdash;', '&ndash;', '&#8211;', '&#8212;' );
		$this->text = str_replace( $tires, '—', $this->text );

		$pre = '(январь|февраль|март|апрель|июнь|июль|август|сентябрь|октябрь|ноябрь|декабрь)';
		$this->text = preg_replace( '/' . $pre . ' ?(-|—) ?' . $pre . '/i', '$1—$3', $this->text );

		$pre = '(понедельник|вторник|среда|четверг|пятница|суббота|воскресенье)';
		$this->text = preg_replace( '/' . $pre . ' ?(-|—) ?' . $pre . '/i', '$1—$3', $this->text );

		$this->text = preg_replace( '/(^|\n|>) ?(-|—) /', '$1— ', $this->text );

		$this->text = preg_replace( '/(^|[^\d\-])(\d{1,4}) ?(—|-) ?(\d{1,4})([^\d\-\=]|$)/',
					    '$1$2' .
					    $this->tireinterval . '$4$5',
					    $this->text );

		$this->text = preg_replace( '/ -(?= )/', $this->space . $this->tire, $this->text );
		$this->text = preg_replace( '/(?<=&nbsp;|&#160;)-(?= )/', $this->tire, $this->text );

		$this->text = preg_replace( '/ —(?= )/', $this->space . $this->tire, $this->text );
		$this->text = preg_replace( '/(?<=&nbsp;|&#160;)—(?= )/', $this->tire, $this->text );

		return;
	}

	/**
	 * Заменяет br на p и наоборот
	 */
	private function pbr() {

		$n = strpos( $this->text, "\n" );

		if( $this->_isHTMLCode ) {
			return;
		}

		if( $n !== false ) {
			if( $this->br ) {
				if( !$this->p ) {
					$this->text = str_replace( "\n", "<br />\n", $this->text );
				} else {
					$this->text = preg_replace( '/^([^\n].*?)(?=\n\n)/s', '<p>$1</p>', $this->text );
					$this->text = preg_replace( '/(?<=\n\n)([^\n\<].*?)(?=\n\n)/s', '<p>$1</p>', $this->text );
					$this->text = preg_replace( '/(?<=\n\n)([^\n\<].*?)$/s', '<p>$1</p>', $this->text );

					$this->text = preg_replace( '/([^\n])\n([^\n])/', "$1<br />\n$2", $this->text );
				}
			} else {
				if( $this->p ) {
					$this->text = preg_replace( '/^([^\n].*?)(?=\n\n)/s', '<p>$1</p>', $this->text );
					$this->text = preg_replace( '/(?<=\n\n)([^\n].*?)(?=\n\n)/s', '<p>$1</p>', $this->text );
					$this->text = preg_replace( '/(?<=\n\n)([^\n].*?)$/s', '<p>$1</p>', $this->text );
				}
			}
		} else {
			if( $this->p ) {
				$this->text = '<p>' . $this->text . '</p>';
			}
		}
	}

	/**
	 * Обрабатывает все варианты пробелов
	 */
	private function spaces() {

		$this->text = str_replace( "\r", '', $this->text );

		if( $this->delTab ) {
			$this->text = str_replace( "\t", '', $this->text );
		} elseif( $this->replaceTab ) {
			$this->text = str_replace( "\t", ' ', $this->text );
		}

		$this->text = trim( $this->text );

		$this->text = str_replace( '&nbsp;', ' ', $this->text );
		$this->text = str_replace( '&#160;', ' ', $this->text );

		if( $this->delRepeatSpace ) {
			$this->text = preg_replace( '/ {2,}/', ' ', $this->text );
			$this->text = preg_replace( "/\n {1,}/m", "\n", $this->text );
			$this->text = preg_replace( "/\n{3,}/m", "\n\n", $this->text );
		}

		if( $this->delSpaceBeforePunctuation ) {
			$before = array( '!', ';', ',', '?', '.', ')', );
			$after = array();
			foreach( $before as $i ) {
				$after[] = '/ \\' . $i . '/';
			}
			$this->text = preg_replace( $after, $before, $this->text );
			$this->text = preg_replace( '/\( /', '(', $this->text );
		}

		if( $this->spaceBeforeParticles ) {
			$this->text = preg_replace( '/ (ли|ль|же|ж|бы|б)(?![а-яА-Я])/', $this->space . '$1', $this->text );
		}

		if( $this->spaceAfterShortWord and $this->lengthShortWord > 0 ) {
			$this->text = preg_replace( '/( [а-яА-Я\w]{1,' . $this->lengthShortWord . '}) /', '$1' . $this->space, $this->text );
		}

		if( $this->spaceBeforeLastWord and $this->lengthLastWord > 0 ) {
			$this->text = preg_replace( '/ ([а-яА-Я\w]{1,' . $this->lengthLastWord . '})(?=\.|\?|:|\!|,)/', $this->space . '$1', $this->text );
		}

		if( $this->spaceAfterNum ) {
			$this->text = preg_replace( '/(№|&#8470;)(?=\d)/', $this->number . $this->space, $this->text );
			$this->text = preg_replace( '/(§|&#167;|&sect;)(?=\d)/', $this->sect . $this->space, $this->text );
		}

		if( $this->delSpaceBeforeProcent ) {
			$this->text = preg_replace( '/( |&nbsp;|&#160;)%/', '%', $this->text );
		}
	}

	/**
	 * Заменяет символы на utf эквивалент
	 */
	private function replaceWindowsCodes() {

		$after = array(
			'&#167;',
			'&#169;',
			'&#174;',
			'&#8482;',
			'&#176;',
			'&#171;',
			'&#183;',
			'&#187;',
			'&#133;',
			'&#8216;',
			'&#8217;',
			'&#8220;',
			'&#8221;',
			'&#164;',
			'&#166;',
			'&#8222;',
			'&#8226;',
			'&#8211;',
			$this->plusmn,
			$this->tire,
			$this->number,
			'&#8240;',
			'&#8364;',
			'&#182;',
			'&#172;'
		);

		$before = array(
			'§',
			'©',
			'®',
			'™',
			'°',
			'«',
			'·',
			'»',
			'…',
			'‘',
			'’',
			'“',
			'”',
			'¤',
			'¦',
			'„',
			'•',
			'–',
			'±',
			'—',
			'№',
			'‰',
			'€',
			'¶',
			'¬'
		);

		$this->text = str_replace( $before, $after, $this->text );
	}

	/**
	 * Возвращает настройки типографа
	 *
	 * @static
	 *
	 * @param \DOMElement|\DOMNode $node
	 */
	public static function getSettingsXML( $node ) {

		$Typo = new self;
		$node->setAttribute( 'spaceAfterShortWord', $Typo->spaceAfterShortWord );
		$node->setAttribute( 'lengthShortWord', $Typo->lengthShortWord );
		$node->setAttribute( 'spaceBeforeLastWord', $Typo->spaceBeforeLastWord );
		$node->setAttribute( 'lengthLastWord', $Typo->lengthLastWord );
		$node->setAttribute( 'spaceAfterNum', $Typo->spaceAfterNum );
		$node->setAttribute( 'spaceBeforeParticles', $Typo->spaceBeforeParticles );
		$node->setAttribute( 'delRepeatSpace', $Typo->delRepeatSpace );
		$node->setAttribute( 'delSpaceBeforePunctuation', $Typo->delSpaceBeforePunctuation );
		$node->setAttribute( 'delSpaceBeforeProcent', $Typo->delSpaceBeforeProcent );
		$node->setAttribute( 'doReplaceBefore', $Typo->doReplaceBefore );
		$node->setAttribute( 'doReplaceAfter', $Typo->doReplaceAfter );
		$node->setAttribute( 'doMacros', $Typo->doMacros );
	}
}