<?php


/**
 * Класс для обработки статей
 */
class Article
{
    // Свойства
    /**
    * @var int ID статей из базы данны
    */
    public $id = null;

    /**
    * @var int Дата первой публикации статьи
    */
    public $publicationDate = null;

    /**
    * @var string Полное название статьи
    */
    public $title = null;

     /**
    * @var int ID категории статьи
    */
    public $categoryId = null;

     /**
    * @var int ID подкатегории статьи
    */
    public $subcategoryId = null;

    /**
    * @var string Краткое описание статьи
    */
    public $summary = null;

    /**
    * @var string HTML содержание статьи
    */
    public $content = null;

    /**
    * @var string Возможность просмотра статьи
    */
    public $active = null;

    /**
    * @var string Авторы статьи
    */
    public $authors = [];

    /**
    * Устанавливаем свойства с помощью значений в заданном массиве
    *
    * @param assoc Значения свойств
    */

    /*
    public function __construct( $data=array() ) {
      if ( isset( $data['id'] ) ) {$this->id = (int) $data['id'];}
      if ( isset( $data['publicationDate'] ) ) {$this->publicationDate = (int) $data['publicationDate'];}
      if ( isset( $data['title'] ) ) {$this->title = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['title'] );}
      if ( isset( $data['categoryId'] ) ) {$this->categoryId = (int) $data['categoryId'];}
      if ( isset( $data['summary'] ) ) {$this->summary = preg_replace ( "/[^\.\,\-\_\'\"\@\?\!\:\$ a-zA-Z0-9()]/", "", $data['summary'] );}
      if ( isset( $data['content'] ) ) {$this->content = $data['content'];}
    }*/
    
    /**
     * Создаст объект статьи
     * 
     * @param array $data массив значений (столбцов) строки таблицы статей
     */
    public function __construct($data=array())
    {
        
      if (isset($data['id'])) {
          $this->id = (int) $data['id'];
      }
      
      if (isset( $data['publicationDate'])) {
          $this->publicationDate = (string) $data['publicationDate'];     
      }

      //die(print_r($this->publicationDate));

      if (isset($data['title'])) {
          $this->title = $data['title'];        
      }
      
      if (isset($data['categoryId'])) {
          $this->categoryId = (int) $data['categoryId'];      
      }

      if (isset($data['subcategoryId'])) {
        $this->subcategoryId = (int) $data['subcategoryId'];      
      }

      if(isset($data['authors'])) {
        foreach($data['authors'] as $author){
            $this->authors[] = $author;
        }
      }
      
      if (isset($data['summary'])) {
          $this->summary = $data['summary'];         
      }
      
      if (isset($data['content'])) {
          $this->content = $data['content'];  
      }

      if(isset($data['active'])) {
        $this->active = $data['active'];
      } else {
        $this->active = 0;
      }

    }

    /**
    * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
    *
    * @param assoc Значения записи формы
    */
    public function storeFormValues ( $params ) {

      // Сохраняем все параметры
      $this->__construct( $params );

      // Разбираем и сохраняем дату публикации
      if ( isset($params['publicationDate']) ) {
        $publicationDate = explode ( '-', $params['publicationDate'] );

        if ( count($publicationDate) == 3 ) {
          list ( $y, $m, $d ) = $publicationDate;
          $this->publicationDate = mktime ( 0, 0, 0, $m, $d, $y );
        }
      }
    }


    /**
    * Возвращаем объект статьи соответствующий заданному ID статьи
    *
    * @param int ID статьи
    * @return Article|false Объект статьи или false, если запись не найдена или возникли проблемы
    */
    public static function getById($id) {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT *, UNIX_TIMESTAMP(publicationDate) "
                . "AS publicationDate FROM articles WHERE id = :id";
        $st = $conn->prepare($sql);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();

        $row = $st->fetch();

        $sql = "SELECT DISTINCT idUser FROM users_articles WHERE idArticle=:idArticle";
        $st = $conn->prepare($sql);
        $st->bindValue(":idArticle", $id, PDO::PARAM_INT);
        $st->execute();
        
        $authors = array();
        while($author = $st->fetch()){
            $authors[] = $author['idUser'];
        }

        $row['authors'] = $authors;

        $conn = null;
        
        if ($row) { 
            return new Article($row);
        }
    }


    /**
    * Возвращает все (или диапазон) объекты Article из базы данных
    *
    * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
    * @param int $categoryId Вернуть статьи только из категории с указанным ID
    * @param int $subcategoryId Вернуть статьи только из подкатегории с указанным ID
    * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
    * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
    */
    public static function getList($numRows=1000000, $categoryId=null, $subcategoryId=null, $active = false, $order="publicationDate DESC")
    {
        $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);

        if($active === false) {
          if($categoryId) {
            $clause = "WHERE categoryId = :categoryId";
          } elseif($subcategoryId) {
            $clause = "WHERE subcategoryId = :categoryId";
          } else {
            $clause = "";
          }
            $clause = $categoryId ? "WHERE categoryId = :categoryId" : "";
        } 
        else {
          if($categoryId) {
            $clause = "WHERE categoryId = :categoryId AND active = " . $active;
          } elseif($subcategoryId) {
            $clause = "WHERE subcategoryId = :subcategoryId AND active = " . $active;
          } else {
            $clause = "WHERE active = " . $active;
          }
        }

        $sql = "SELECT SQL_CALC_FOUND_ROWS *, UNIX_TIMESTAMP(publicationDate) 
                AS publicationDate
                FROM articles $clause
                ORDER BY  $order  LIMIT :numRows";
        
        $st = $conn->prepare($sql);
//                        echo "<pre>";
//                        print_r($st);
//                        echo "</pre>";
//                        Здесь $st - текст предполагаемого SQL-запроса, причём переменные не отображаются
        $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);
        
        if ($categoryId) {
            $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT);
        } elseif($subcategoryId) {
            $st->bindValue( ":subcategoryId", $subcategoryId, PDO::PARAM_INT);
        }
        $st->execute(); // выполняем запрос к базе данных
//                        echo "<pre>";
//                        print_r($st);
//                        echo "</pre>";
//                        Здесь $st - текст предполагаемого SQL-запроса, причём переменные не отображаются
        $list = array();

        while ($row = $st->fetch()) {
            $article = new Article($row);
            $list[] = $article;
        }

        // Получаем общее количество статей, которые соответствуют критерию
        $sql = "SELECT FOUND_ROWS() AS totalRows";
        $totalRows = $conn->query($sql)->fetch();

        // Получаем авторов статьи
        foreach ($list as $article){
          $sql = "SELECT DISTINCT idUser FROM users_articles WHERE idArticle=:idArticle";
          $st = $conn->prepare($sql);
          $st->bindValue(":idArticle", $article->id, PDO::PARAM_INT);
          $st->execute();
          $authors = array();

          while ($author = $st->fetch()) {
              $authors[] = $author['idUser'];
          }
          $article->authors = $authors;
        }

        $conn = null;
        
        return (array(
            "results" => $list, 
            "totalRows" => $totalRows[0]
            ) 
        );
    }


    /**
    * Вставляем текущий объект статьи в базу данных, устанавливаем его свойства.
    */


    /**
    * Вставляем текущий объек Article в базу данных, устанавливаем его ID.
    */
    public function insert() {

        // Есть уже у объекта Article ID?
        if ( !is_null( $this->id ) ) trigger_error ( "Article::insert(): Attempt to insert an Article object that already has its ID property set (to $this->id).", E_USER_ERROR );

        // Вставляем статью
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        
        $sql = "INSERT INTO articles ( publicationDate, categoryId, subcategoryId, title, summary, content, active )  
        VALUES ( FROM_UNIXTIME(:publicationDate), :categoryId, :subcategoryId, :title, :summary, :content, :active)";        
        $st = $conn->prepare ( $sql );
        $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
        $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
        $st->bindValue( ":subcategoryId", $this->subcategoryId, PDO::PARAM_INT );
        $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
        $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
        $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
        $st->bindValue( ":active", $this->active, PDO::PARAM_INT);
        $st->execute();
        $this->id = $conn->lastInsertId();
        
        //Вставляем авторов
        foreach ($this->authors as $idUser) {
          $sql = "INSERT INTO users_articles (idUser, idArticle) VALUES (:idUser, :idArticle)";
          $st = $conn->prepare($sql);
          $st->bindValue(":idUser", $idUser, PDO::PARAM_INT);
          $st->bindValue(":idArticle", $this->id, PDO::PARAM_INT);
          $st->execute();
        }


        $conn = null;
    }

    /**
    * Обновляем текущий объект статьи в базе данных
    */
    public function update() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::update(): "
              . "Attempt to update an Article object "
              . "that does not have its ID property set.", E_USER_ERROR );

      // Обновляем статью
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE articles SET publicationDate=FROM_UNIXTIME(:publicationDate), 
      categoryId=:categoryId, subcategoryId=:subcategoryId,
      title=:title, summary=:summary, content=:content, active=:active 
      WHERE id = :id";
      
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":publicationDate", $this->publicationDate, PDO::PARAM_INT );
      $st->bindValue( ":categoryId", $this->categoryId, PDO::PARAM_INT );
      $st->bindValue( ":subcategoryId", $this->subcategoryId, PDO::PARAM_INT );
      $st->bindValue( ":title", $this->title, PDO::PARAM_STR );
      $st->bindValue( ":summary", $this->summary, PDO::PARAM_STR );
      $st->bindValue( ":content", $this->content, PDO::PARAM_STR );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->bindValue(":active", $this->active, PDO::PARAM_INT);
      $st->execute();

      // Удаляем записи о статье в таблице связи
      $sql = "DELETE FROM users_articles WHERE idArticle=:idArticle";
      $st = $conn->prepare($sql);
      $st->bindValue(":idArticle", $this->id, PDO::PARAM_INT);
      $st->execute();

      // Добавляем новые записи в таблицу связи
      foreach ($this->authors as $idUser) {
        $sql = "INSERT INTO users_articles (idUser, idArticle) VALUES (:idUser, :idArticle)";
        $st = $conn->prepare($sql);
        $st->bindValue(":idUser", $idUser, PDO::PARAM_INT);
        $st->bindValue(":idArticle", $this->id, PDO::PARAM_INT);
        $st->execute();
      }

      $conn = null;
    }

    /**
    * Удаляем текущий объект статьи из базы данных
    */
    public function delete() {

      // Есть ли у объекта статьи ID?
      if ( is_null( $this->id ) ) trigger_error ( "Article::delete(): Attempt to delete an Article object that does not have its ID property set.", E_USER_ERROR );

      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );

      // Удаляем записи в таблице связи
      $sql = "DELETE FROM users_articles WHERE idArticle=:idArticle";
      $st = $conn->prepare($sql);
      $st->bindValue(":idArticle", $this->id, PDO::PARAM_INT);
      $st->execute();

      // Удаляем статью
      $st = $conn->prepare ( "DELETE FROM articles WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

    /**
    * Получаем имена авторов по id статьи
    */
    public static function getAuthorsById($id)
    {
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "SELECT users.username FROM users_articles 
              LEFT JOIN users ON idUser = users.id 
              WHERE users_articles.idArticle = :id ";
      $st = $conn->prepare($sql);
      $st->bindValue(":id", $id, PDO::PARAM_INT);
      $st->execute();
      $conn = null;
      $list = array();
      while ($row = $st->fetch()) {
        $list[] = $row['username'];
      }

      return $list;
    }

    public static function checkSubcategory($categoryId, $subcategoryId)
    {
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "SELECT COUNT(*) FROM subcategory WHERE idCategory=:categoryId AND id=:subcategoryId";
      $st = $conn->prepare($sql);
      $st->bindValue( ":categoryId", $categoryId, PDO::PARAM_INT );
      $st->bindValue( ":subcategoryId", $subcategoryId, PDO::PARAM_INT );
      $st->execute();
      $totalRows = $st->fetch();
      $conn = null;
      return $totalRows;
    }
}
