<?php

namespace Difra\Plugins\FormProcessor;

class form {

    private $loaded = true;
    private $modified = false;

    private $id = null;
    private $title = null;
    private $uri = null;
    private $description = '';
    private $answer = null;
    private $submit = null;
    private $status = 0;
    private $fields = null;

    /**
     * Для дебага
     * @var int
     */
    private $n = 0;

    public function __destruct() {

        if( $this->modified && $this->loaded ) {
            $this->save();
            self::cleanCache();
        }
    }

    /**
     * Создаёт объект формы
     * @static
     * @return form
     */
    public static function create() {

        return new self;
    }

    /**
     * Получение формы по её id
     * @static
     * @param $id
     */
    public static function get( $id ) {

        $form = new self;
        $form->id = intval( $id );
        $form->loaded = false;
        return $form;
    }

    /**
     * Загрузка формы
     */
    private function load() {

        if( $this->loaded ) {
            return true;
        }
        if( !$this->id ) {
            return false;
        }

        $cachedData = \Difra\Cache::getInstance()->get( 'fp_forms' );
        $data = null;

        if( !$cachedData ) {
            $db = \Difra\MySQL::getInstance();
            $query = "SELECT * FROM `fp_forms` WHERE `id`='" . intval( $this->id ) . "'";
            $data = $db->fetchRow( $query );
            $this->n ++;
        } else {
            if( isset( $cachedData[$this->id] ) && is_array( $cachedData[$this->id] ) ) {
                $data = $cachedData[$this->id];
            }
        }

        if( !$data ) {
            return false;
        }

        $this->title = $data['title'];
        $this->status = $data['hidden'];
        $this->uri = $data['uri'];
        $this->description = $data['description'];
        $this->answer = $data['answer'];
        $this->submit = $data['submit'];
        $this->fields = unserialize( $data['fields'] );

        $this->loaded = true;
        return true;
    }

    /**
     * Определяет соответствует ли текущая страница странице с формой
     * @static
     *
     */
    public static function find() {

        $uri = '/' . \Difra\Action::getInstance()->getUri();
        $Cache = \Difra\Cache::getInstance();
        $data = $Cache->get( 'fp_forms' );
        if( !$data ) {
            // нет данных в кэше? установить!

            $db = \Difra\MySQL::getInstance();
            $query = "SELECT * FROM `fp_forms`";
            $res = $db->fetch( $query );
            if( !empty( $res ) ) {
                foreach( $res as $k=>$fData ) {
                    $data[$fData['id']] = $fData;
                }

                $Cache->put( 'fp_forms', $data, 10800 );
            }
        }

        // ищем нужную страницу
        if( !empty( $data ) ) {
            foreach( $data as $k=>$form ) {
                if( isset( $form['uri'] ) && $form['uri'] == $uri && $form['hidden'] == 0 ) {
                    return $form['id'];
                }
            }
        }
        return false;
    }

    /**
     * Устанавливает название формы
     * @param $title
     */
    public function setTitle( $title ) {

        $this->load();
        $this->title = trim( $title );
        $this->modified = true;
    }

    /**
     * устанавливает адрес формы
     * @param $uri
     */
    public function setUri( $uri ) {

        $this->load();
        if( mb_substr( trim( $uri ), 0, 1 ) != '/' ) {
            $uri = '/' . trim( $uri );
        }
        $this->uri = $uri;
        $this->modified = true;
    }

    /**
     * Устанавливает ответ на отправку формы
     * @param $answer
     */
    public function setAnswer( $answer ) {

        $this->load();
        $this->answer = trim( $answer );
        $this->modified = true;
    }

    /**
     * Устанавливает текст на кнопке отправки формы
     * @param $submitText
     */
    public function setSubmit( $submitText ) {

        $this->load();
        $this->submit = trim( $submitText );
        $this->modified = true;
    }

    /**
     * Устанавливает пользовательские поля формы
     * @param $fieldsArray
     */
    public function setFormFields( $fieldsArray ) {

        $this->load();
        $fields = array();

        // обрабатываем все возможные поля
        $index = 0;
        $variantsIndex = 0;
        foreach( $fieldsArray['types'] as $n => $type ) {

            $fieldArray[$index] = array( 'type' => $type, 'name' => $fieldsArray['names'][$n], 'description' => $fieldsArray['descriptions'][$n] );

            if( isset( $fieldsArray['mandatory'][$n] ) ) {
                $fieldArray[$index]['mandatory'] = intval( $fieldsArray['mandatory'][$n] );
            } else {
                $fieldArray[$index]['mandatory'] = 0;
            }

            if( $type == 'select' || $type == 'radio' ) {

                if( isset( $fieldsArray['variants'][$variantsIndex] ) && $fieldsArray['variants'][$variantsIndex]!='' ) {

                    $variants = explode( ';', $fieldsArray['variants'][$variantsIndex] );
                    if( !empty( $variants ) ) {
                        $vArray = null;
                        foreach( $variants as $vn => $variant ) {
                            if( trim( $variant ) !='' ) {
                                $vArray[] = trim( $variant );
                            }
                        }
                        $fieldArray[$index]['variants'] = $vArray;
                        $variantsIndex++;
                    }
                } else {
                    $fieldArray[$index]['variants'] = null;
                }
            } else {
                $fieldArray[$index]['variants'] = null;
            }

            $index++;
        }

        $this->fields = $fieldArray;
        $this->modified = true;
    }

    /**
     * Устанавливает описание формы
     * @param $description
     */
    public function setDescription( $description ) {

        $this->load();
        $this->description = $description;
        $this->modified = true;
    }

    /**
     * Устанавливает статус
     * @param $status
     */
    public function setStatus( $status ) {

        $this->load();
        $this->status = intval( $status );
        $this->modified = true;
    }

    /**
     * Сохраняет или обновляет данные формы, в случае если есть её id.
     */
    private function save() {

        $db = \Difra\MySQL::getInstance();

        $fields = serialize( $this->fields );

        if( is_null( $this->id ) ) {

            // сохранение записи в базе данных
            $query = "INSERT INTO `fp_forms` (`uri`, `title`, `answer`, `submit`, `fields`)
                        VALUES ('" . $db->escape( $this->uri ) . "', '" . $db->escape( $this->title ) . "', '" . $db->escape( $this->answer ) .
                                    "', '" . $db->escape( $this->submit ) . "', '" . $db->escape( $fields ) . "')";

            $db->query( $query );
            $this->n++;
            $this->id = $db->getLastId();

        } else {
            // апдейт записи

            $query = "UPDATE `fp_forms` SET `uri`='" . $db->escape( $this->uri ) . "', `title`='" . $db->escape( $this->title ) .
                        "', `answer`='" . $db->escape( $this->answer ) . "', `submit`='" . $db->escape( $this->submit ) .
                        "', `fields`='" . $db->escape( $fields ) . "', `hidden`='" . intval( $this->status ) . "' WHERE `id`='" . intval( $this->id ) . "'";
            $db->query( $query );
            $this->n++;
        }

        // сохранение картинок и апдейт описания формы

        if( $this->description instanceof \Difra\Param\AjaxHTML or $this->description instanceof \Difra\Param\AjaxSafeHTML ) {
            $this->description->saveImages( DIR_DATA . 'forms/img/' . $this->id, '/fpimg/' . $this->id );
            $this->description = $this->description->val();
        }

        $db->query( "UPDATE `fp_forms` SET `description`='" . $db->escape( $this->description ) . "' WHERE `id`='" . intval( $this->id ) . "'" );
        $this->n++;
    }

    /**
     * Чистит кэш
     * @static
     *
     */
    private static function cleanCache() {

        \Difra\Cache::getInstance()->remove( 'fp_forms' );
    }

    /**
     * Удаление формы
     */
    public function delete() {

        $this->loaded = true;
        $this->modified = false;

        if( $this->id ) {
            $path = DIR_DATA . 'forms/img/' . $this->id;
            if( is_dir( $path ) ) {
                $dir = opendir( $path );
                while( false !== ( $file = readdir( $dir ) ) ) {
                    if( $file{0} == '.' ) {
                        continue;
                    }
                    @unlink( "$path/$file" );
                }
                rmdir( $path );
            }
        }

        $db = \Difra\MySQL::getInstance();
        $db->query( "DELETE FROM `fp_forms` WHERE `id`='" . intval( $this->id ) . "'" );
        self::cleanCache();
    }

    /**
     * Возвращает статус формы
     * @return int
     */
    public function getStatus() {

        $this->load();
        return $this->status;
    }

    /**
     * Возвращает название формы
     * @return string
     */
    public function getTitle() {

        $this->load();
        return $this->title;
    }

    /**
     * Возвращает URI формы
     * @return string
     */
    public function getUri() {

        $this->load();
        return $this->uri;
    }

    /**
     * Возвращает описание формы
     * @return string
     */
    public function getDescription() {

        $this->load();
        return $this->description;
    }

    /**
     * Возвращает ответ на отправку формы
     * @return string
     */
    public function getAnswer() {

        $this->load();
        return $this->answer;
    }

    /**
     * Возвращает текст кнопки отправки формы
     * @return string
     */
    public function getSubmit() {

        $this->load();
        return $this->submit;
    }

    /**
     * Возвращает массив с полями формы
     * @return array
     */
    public function getFields() {

        $this->load();
        return $this->fields;
    }
}