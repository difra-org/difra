<?php

namespace Difra\Plugins;

class FormProcessor
{
    /**
     * @static
     * @return FormProcessor
     */
    static public function getInstance()
    {

        static $_instance = null;
        return $_instance ? $_instance : $_instance = new self;
    }

    /**
     * Проверяет массив полей создаваемой формы на пустые строки
     * @param $$fieldsTypeArray
     * @param $fieldsNameArray
     * @return bool
     */
    public function checkEmptyNameFields($fieldsTypeArray, $fieldsNameArray)
    {

        $fieldsNameArray = $fieldsNameArray->val();
        $fieldsTypeArray = $fieldsTypeArray->val();

        foreach ($fieldsTypeArray as $num => $string) {
            if ($string == '') {
                return 'fieldName[' . $num . ']';
            }
        }

        foreach ($fieldsNameArray as $num => $string) {
            if ($string == '') {
                return 'fieldName[' . $num . ']';
            }
        }
        return true;
    }

    /**
     * Создаёт форму
     * @param array $mainFields
     * @param array $formFields
     */
    public function createForm($mainFields, $formFields)
    {

        $Form = \Difra\Plugins\FormProcessor\Form::create();
        $Form->setTitle($mainFields['title']);
        $Form->setUri($mainFields['uri']);
        $Form->setAnswer($mainFields['answer']);
        $Form->setSubmit($mainFields['submit']);
        $Form->setDescription($mainFields['description']);

        $Form->setFormFields($formFields);
    }

    /**
     * Апдейт формы
     * @param $mainFields
     * @param $formFields
     */
    public function updateForm($formId, $mainFields, $formFields)
    {

        $Form = \Difra\Plugins\FormProcessor\Form::get($formId);
        if (is_null($Form->getTitle())) {
            return false;
        }

        $Form->setTitle($mainFields['title']);
        $Form->setUri($mainFields['uri']);
        $Form->setAnswer($mainFields['answer']);
        $Form->setSubmit($mainFields['submit']);
        $Form->setDescription($mainFields['description']);

        $Form->setFormFields($formFields);

        return true;
    }

    /**
     * Проверяет на дубликаты uri форм
     * @param $uri
     * @return bool
     */
    public function checkDupUri($uri)
    {

        if (mb_substr(trim($uri), 0, 1) != '/') {
            $uri = '/' . trim($uri);
        }

        $Cache = \Difra\Cache::getInstance();
        $forms = $Cache->get('fp_forms');

        if (!$forms) {

            $db = \Difra\MySQL::getInstance();
            $query = "SELECT `id` FROM `fp_forms` WHERE `uri`='" . $db->escape($uri) . "'";
            $res = $db->fetchOne($query);
            return isset($res[0]) ? true : false;
        } else {
            foreach ($forms as $k => $data) {
                if (isset($data['uri']) && $data['uri'] == $uri) {
                    return true;
                }
            }
            return false;
        }
    }

    /**
     * Возвращает все формы в XML
     * @param \DOMNode $node
     */
    public function getListXML($node)
    {

        $cached = true;
        $Cache = \Difra\Cache::getInstance();
        $forms = $Cache->get('fp_forms');
        $formsArray = null;

        if (!$forms) {
            $cached = false;
            $db = \Difra\MySQL::getInstance();
            $query = "SELECT * FROM `fp_forms`";
            $forms = $db->fetch($query);
        }

        foreach ($forms as $k => $data) {

            $formXML = $node->appendChild($node->ownerDocument->createElement('form'));
            $formXML->setAttribute('id', $data['id']);
            $formXML->setAttribute('title', $data['title']);
            $formXML->setAttribute('uri', $data['uri']);
            $formXML->setAttribute('hidden', $data['hidden']);
            $formXML->setAttribute('fieldsCount', count(unserialize($data['fields'])));
            if (!$cached) {
                $formsArray[$data['id']] = $data;
            }
        }

        if (!$cached && !empty($formsArray)) {
            // устанавливаем кэш
            $Cache->put('fp_forms', $formsArray, 10800);
        }
    }

    /**
     * Включает или выключает форму
     * @param $formId
     */
    public function changeStatus($formId)
    {

        $Form = \Difra\Plugins\FormProcessor\Form::get($formId);
        if ($Form->getStatus() == 0) {
            $Form->setStatus(1);
        } else {
            $Form->setStatus(0);
        }
    }

    /**
     * Удаляет форму
     * @param $formId
     */
    public function deleteForm($formId)
    {

        $Form = \Difra\Plugins\FormProcessor\Form::get($formId);
        $Form->delete();
    }

    /**
     * Возвращает XML формы по её id
     * @param \DOMNode $node
     */
    public function getFormXML($node, $formId)
    {

        $Form = \Difra\Plugins\FormProcessor\Form::get($formId);

        if (is_null($Form->getTitle())) {
            return false;
        }

        $node->setAttribute('id', $formId);
        $node->setAttribute('title', $Form->getTitle());
        $node->setAttribute('uri', $Form->getUri());
        $node->setAttribute('answer', $Form->getAnswer());
        $node->setAttribute('submit', $Form->getSubmit());
        $node->setAttribute('description', $Form->getDescription());

        $fields = $Form->getFields();
        $fieldsNode = $node->appendChild($node->ownerDocument->createElement('fields'));

        foreach ($fields as $k => $data) {
            $fieldNode = $fieldsNode->appendChild($node->ownerDocument->createElement('field'));
            $fieldNode->setAttribute('type', $data['type']);
            $fieldNode->setAttribute('name', $data['name']);
            $fieldNode->setAttribute('description', $data['description']);
            $fieldNode->setAttribute('mandatory', $data['mandatory']);

            if ($data['type'] == 'select' || $data['type'] == 'radio' && !empty($data['variants'])) {
                $variantsNode = $fieldNode->appendChild($node->ownerDocument->createElement('variants'));
                foreach ($data['variants'] as $n => $vData) {
                    $variantNode = $variantsNode->appendChild($node->ownerDocument->createElement('variant'));
                    $variantNode->setAttribute('value', $vData);
                }
            }
        }
        return true;
    }

    /**
     * Определяет зашел ли пользователь на страницу формы и запускает все нужные процесса для её отображения
     */
    public function run()
    {

        if ($formId = \Difra\Plugins\FormProcessor\Form::find()) {
            $action = \Difra\Action::getInstance();
            $action->className = '\Difra\Plugins\FormProcessor\Controller';
            $action->method = 'formAction';
            $action->parameters = [$formId];
        }
    }
}
