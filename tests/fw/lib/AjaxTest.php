<?php

class AjaxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @backupGlobals enabled
     */
    public function test_actions()
    {
        \Difra\Debugger::disable();

        \Difra\Ajaxer::clean();
        $actions = [];

        \Difra\Ajaxer::notify('notification message');
        $actions[] = [
            'action' => 'notify',
            'message' => 'notification message',
            'lang' => ['close' => \Difra\Locales::get('notifications/close')]
        ];

        \Difra\Ajaxer::display('<span>test</span>');
        $actions[] = [
            'action' => 'display',
            'html' => '<span>test</span>'
        ];

        \Difra\Ajaxer::error('error message <span>test</span>');
        $actions[] = [
            'action' => 'error',
            'message' => 'error message &lt;span&gt;test&lt;/span&gt;',
            'lang' => [
                'close' => \Difra\Locales::get('notifications/close')
            ]
        ];

        \Difra\Ajaxer::required('element');
        $actions[] = [
            'action' => 'require',
            'name' => 'element'
        ];

        \Difra\Ajaxer::invalid('inv1');
        $actions[] = [
            'action' => 'invalid',
            'name' => 'inv1'
        ];

        \Difra\Ajaxer::invalid('inv2');
        $actions[] = [
            'action' => 'invalid',
            'name' => 'inv2'
        ];

        \Difra\Ajaxer::status('field1', 'bad value', 'problem');
        $actions[] = [
            'action' => 'status',
            'name' => 'field1',
            'message' => 'bad value',
            'classname' => 'problem'
        ];

        \Difra\Ajaxer::redirect('/some/page');
        $actions[] = [
            'action' => 'redirect',
            'url' => '/some/page'
        ];

        $_SERVER['HTTP_REFERER'] = '/current/page';
        \Difra\Ajaxer::refresh();
        $actions[] = [
            'action' => 'redirect',
            'url' => '/current/page'
        ];

        \Difra\Ajaxer::reload();
        $actions[] = [
            'action' => 'reload'
        ];

        \Difra\Ajaxer::load('someid', 'some <b>content</b>');
        $actions[] = [
            'action' => 'load',
            'target' => 'someid',
            'html' => 'some <b>content</b>',
            'replace' => false
        ];

        \Difra\Ajaxer::close();
        $actions[] = [
            'action' => 'close'
        ];

        \Difra\Ajaxer::reset();
        $actions[] = [
            'action' => 'reset'
        ];

        \Difra\Envi::setUri('/current/page');
        \Difra\Ajaxer::confirm('Are you sure?');
        $actions[] = [
            'action' => 'display',
            'html' => '<form action="/current/page" class="ajaxer"><input type="hidden" name="confirm" value="1"/>' .
                      '<div>Are you sure?</div>' .
                      '<input type="submit" value="' . \Difra\Locales::get('ajaxer/confirm-yes') . '"/>' .
                      '<input type="button" value="' . \Difra\Locales::get('ajaxer/confirm-no') .
                      '" onclick="ajaxer.close(this)"/>' .
                      '</form>'
        ];

        $this->assertEquals(
            \Difra\Ajaxer::getResponse(), json_encode(['actions' => $actions], \Difra\Ajaxer::getJsonFlags())
        );

        \Difra\Ajaxer::clean();
        $this->assertEquals(\Difra\Ajaxer::getResponse(), '[]');
        $this->assertFalse(\Difra\Ajaxer::hasProblem());

        \Difra\Ajaxer::reload();
        \Difra\Ajaxer::clean(true);
        $this->assertEquals(\Difra\Ajaxer::getResponse(), '[]');
        $this->assertTrue(\Difra\Ajaxer::hasProblem());
    }
}
