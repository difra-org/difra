<?php

/**
 * Class AdmStatusUnifyController
 * Controller for Unify object tables actions.
 */
class AdmStatusUnifyController extends Difra\Controller
{
    /**
     * Dispatcher
     */
    public function dispatch()
    {
        \Difra\View::$instance = 'adm';
    }

    /**
     * Create table for Unify object
     *
     * @param \Difra\Param\AnyString $name
     */
    public function createAjaxAction(\Difra\Param\AnyString $name)
    {
        try {
            /** @var \Difra\Unify\Item $class */
            $class = \Difra\Unify\Storage::getClass($name->val());
            $class::createDb();
        } catch (\Difra\Exception $ex) {
            \Difra\Ajaxer::notify($ex->getMessage());
        }
        \Difra\Ajaxer::refresh();
    }

    /**
     * Alter table for Unify object
     *
     * @param \Difra\Param\AnyString $name
     */
    public function alterAjaxAction(\Difra\Param\AnyString $name)
    {
        try {
            /** @var \Difra\Unify\Item $class */
            $class = \Difra\Unify\Storage::getClass($name->val());
            $status = $class::getObjDbStatus();
            if ($status['status'] == 'alter') {
                \Difra\MySQL::getInstance()->query($status['sql']);
            }
        } catch (\Difra\Exception $ex) {
            \Difra\Ajaxer::notify($ex->getMessage());
        }
        \Difra\Ajaxer::refresh();
    }
}
