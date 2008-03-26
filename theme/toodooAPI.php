<?php
    class toodooAPI { // (с) Sol, 17.08.2007 22:38:29
    	/**
    	 * Настройки сайта
    	 * @var Array
    	 */
    	public $_config = array ('HOST' => 'http://toodoo.ru','PORT' => 80,'TIMEOUT' => 90);

    	/**
		 * Идентификатора сайта в toodoo
		 * @var int
		 */
    	private $id;

    	/**
    	 * Партнерский ключ (ДЕРЖИТЕ В СЕКРЕТЕ!!!)
    	 * @var string
    	 */
    	private $key;

    	/**
    	 * XML-ответ toodoo
    	 * @var DOMDocument
    	 */
    	public $xml;

    	/**
    	 * Объект с разобранным XML
    	 * @var array
    	 */
    	public $api;

    	/**
    	 * Конструктор с обязательными настройками
    	 * @param int    $blog_id  Идентификатор сайта в toodoo
    	 * @param string $api_key  Партнерский ключ
    	 */
    	public function __construct($blog_id,$api_key) {
    		$this->id  = $blog_id;
            $this->key = $api_key;
            if (extension_loaded('DOM')) $this->_config['DOM'] = true;
    	}
    	
    	/**
    	 * Подготовить строку запроса
    	 * @param  array  $param  Массив параметров
    	 * @return string Строка запроса
    	 */

    	private function prepareQuery ($params)
		{
		    if(!$params) return;
		    foreach ($params as $param => $value) {
		        if (is_array($value)) {
		            foreach ($value as $subvalue) $query .= urlencode($param) . '=' . urlencode($value) . '&';
		            continue;
		        }
		        $query .= $param . '=' . urlencode($value) . '&';
		    }
		    return $query;
		}

    	/**
    	 * Послать запрос  (только для внутреннего использования!)
    	 * @param string   $uri Строка запроса после http://www.toodoo.ru/api/
    	 * @param array    $data Ассоциативный массив с запросом
    	 * @return boolean Результат запроса
    	 */
    	private function request ($uri, $data) {
			$request = array_merge(array ('blog_id' => $this->id, 'key' => $this->key),$data);
			if (function_exists('curl_init')) {
				$ch = curl_init($this->_config['HOST']."/api/{$uri}?".$this->prepareQuery($request));
	    		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
	    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    		curl_setopt($ch, CURLOPT_TIMEOUT, 120);
			    $re = curl_exec($ch);
				curl_close($ch);
			}
			else $re = file_get_contents($this->_config['HOST']."/api/{$uri}?".$this->prepareQuery($request));

			if (isset($this->xml)) unset ($this->xml);
            if ($this->_config['DOM'])
            {
                $this->xml= new DOMDocument();
                $this->xml->loadXML($re);
                unset($this->data); $this->data = (object) array();
    			$this->parse($this->xml->documentElement, $this->api);
            }
            else return false; // здесь надо альтернативный парсер написать
    	}

    	/**
    	 * Создаем объект с разобранным XML
    	 * @param  DOMElement $xml XML-элемент
    	 * @return object Объект с разобранным XML
    	 */
        private function parse ($xml, &$var) {
            if ($xml->childNodes->length > 0) {
            //  Множество дочерних элементов
                $tags = array();
            //  Собираем элементы по именам тегов
                for ($i=0; $i<$xml->childNodes->length; $i++) $tags[$xml->childNodes->item($i)->nodeName][] = $xml->childNodes->item($i);
            //  Перебираем теги и рекурсивно разбираем их
                foreach ($tags as $name => $nodelist) {
                    if (count($nodelist) == 1) {
                    //  Если тег единственный, создаем объект
                        if ($nodelist[0]->nodeName != '#text') {
                            $var->$name = (object) array ();
                            $this->parse ($nodelist[0],$var->$name);
                        }
                        else $var = $nodelist[0]->nodeValue;
                    }
                    else {
                    //  Пропускаем тег #text
                        if ($nodelist[0]->nodeName == '#text') continue;
                        $var->$name = array ();
                        $arr = &$var->$name;
                        for ($i=0; $i<count($nodelist); $i++) {
                            $arr[$i] = (object) array ();
                            $this->parse ($nodelist[$i],$arr[$i]);
                        }
                    }
                }
            }
    	}

    	/**
    	 * Добавить нового пользователя к сети сайта
    	 * @param  array $user Ассоциативный массив ('email' => 'user@domain.net', 'nick_name' => 'UserX', 'password' => 'mySecureK@')
    	 * @param  boolean $confirm Автоматическое подтверждение регистрации (по умолчанию выключено)
    	 * @return int Идентификатор пользователя в tootoo
    	 */
    	public function adduser ($user, $confirm = false) {
			if ($confirm) array_merge($user,array('confirm' => 'on'));
    		$this->request('adduser',$user);
    		return $this->api->user->id;
    	}

    	/**
    	 * Информация о пользователе
    	 * @param  int $id Идентификатор пользователя в toodoo
    	 * @return array Ассоциативный многоуровненвый массив со всей доступной информацией о пользователе
    	 */
        public function user_info ($user) {
            $this->request('user_info',array ('user' => $user));
            return $this->api->user;
    	}

    	/**
    	 * Предложить дружбу
    	 * @param  int     $from От кого
    	 * @param  int     $to Кому
    	 * @param  string  $text Сообщение
    	 * @param  boolean $rank Знакомы лично?
    	 * @return boolean Результат операции
    	 */
		public function requestfriend ($from, $to, $text = null, $rank = false) {
            $this->request('requestfriend',array ('from' => $from,'to' => $to, 'message_text' => $text, 'relation_rank' => ($rank)?'1':'0'));
            if (isset($this->api->ok)) return true; else return false;
		}

		/**
    	 * Принять предложение
    	 * @param  int $from От кого
    	 * @param  int $to Кому
    	 * @return boolean Результат операции
    	 */
		public function confirmfriend ($from, $to, $rank = false) {
            $this->request('confirmfriend',array ('from' => $from,'to' => $to, 'message_text' => $text, 'relation_rank' => ($rank)?'1':'0'));
            if (isset($this->api->ok)) return true; else return false;
		}

		/**
    	 * Отказать
    	 * @param  int $from От кого
    	 * @param  int $to Кому
    	 * @return boolean Результат операции
    	 */
        public function declinefriend ($from, $to) {
            $this->request('declinefriend',array ('from' => $from,'to' => $to));
            if (isset($this->api->ok)) return true; else return false;
		}

		/**
		 * Послать сообщение
    	 * @param  int $from От кого
    	 * @param  int $to Кому
    	 * @param  string $to Текст
    	 * @return boolean Результат операции
         */
		public function sendmessage ($from, $to, $text) {
            $this->request('sendmessage',array ('from' => $from,'to' => $to, 'text' => $text));
            if (isset($this->api->ok)) return true; else return false;
        }
        
        /**
         * Добавить класс сущностей
         * @param $name   Имя сущности
         * @param $weigth Вес оценки для этого типа сущностей
         */

        public function add_entity_type ($name, $weight = 1) {
            $this->request('add_entity_type',array ('name' => $name,'weight' => $weight));
            return $this->api->entity->type;
        }

        /**
         * Голос "ЗА"
         * @param $user        Идентификатор голосующего пользователя
         * @param $owner       Идентификатор владельца сущности
         * @param $entity      Идентификатор сущности
         * @param $entiry_type Тип (класс) сущности
         */
        public function votePositive ($user, $owner, $entity, $entity_type) {
            $this->request('vote',array ('user_id' => $user, 'owner_id' => $owner, 'entity_id' => $entity, 'entity_type' => $entity_type, 'value' => '1'));
            if (isset($this->api->ok)) return true; else return false;
        }

        /**
         * Голос "ПРОТИВ"
         * @param $user        Идентификатор голосующего пользователя
         * @param $owner       Идентификатор владельца сущности
         * @param $entity      Идентификатор сущности
         * @param $entiry_type Тип (класс) сущности
         */
        public function voteNegative ($user, $owner, $entity, $entity_type) {
            $this->request('vote',array ('user_id' => $user, 'owner_id' => $owner, 'entity_id' => $entity, 'entity_type' => $entity_type, 'value' => '-1'));
            if (isset($this->api->ok)) return true; else return false;
        }
        
        /**
         * Рейтинг сущности
         * @param $entity      Идентификатор сущности
         * @param $entiry_type Тип (класс) сущности
         */
        public function entity_rating ($entity, $entity_type) {
            $this->request('entity_rating',array ('entity_id' => $entity, 'entity_type' => $entity_type));
            return $this->api->rate;
        }

        /**
         * Узнать голос пользователя
         * @param $user        Идентификатор пользователя
         * @param $entity      Идентификатор сущности
         * @param $entiry_type Тип (класс) сущности
         */
        public function user_vote ($user, $entity, $entity_type) {
            $this->request('user_vote',array ('user_id' => $user, 'entity_id' => $entity, 'entity_type' => $entity_type));
            return $this->api->rate;
        }

        /**
         * Присоединить пользователя к сети сайта
         * @param $user        Идентификатор пользователя
         */
        public function join ($user) {
            $this->request('join',array ('user_id' => $user));
            return $this->api->ok;
        }

        /**
         * Получить список пользователей в сети сайта
         * @param $page        Количество элементов на странице
         * @param $pagenum     Номер страницы
         */
        public function network ($page = 20, $pagenum = 0) {
            $this->request('network',array ('page' => $page, 'pagenum' => $pagenum));
            return $this->api->users;
        }
    }
?>
