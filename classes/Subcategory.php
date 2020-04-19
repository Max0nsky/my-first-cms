<?php

/**
 * Класс для обработки категорий статей
 */

class Subcategory
{
    // Свойства

    /**
    * @var int ID связанной категории из базы данных
    */
    public $id = null;

    /**
    * @var int ID подкатегории из базы данных
    */
    public $idCategory = null;

    /**
    * @var string Название категории
    */
    public $name = null;

    /**
    * @var string Короткое описание категории
    */
    public $description = null;


    /**
    * Устанавливаем свойства объекта с использованием значений в передаваемом массиве
    *
    * @param assoc Значения свойств
    */


    public function __construct( $data=array() ) {
      if ( isset( $data['id'] ) ) $this->id = (int) $data['id'];
      if ( isset( $data['idCategory'] ) ) $this->idCategory = (int) $data['idCategory'];
      if ( isset( $data['name'] ) ) $this->name = $data['name'];
      if ( isset( $data['description'] ) ) $this->description = $data['description'];
    }

    /**
    * Устанавливаем свойства объекта с использованием значений из формы редактирования
    *
    * @param assoc Значения из формы редактирования
    */

    public function storeFormValues ( $params ) {

      // Store all the parameters
      $this->__construct( $params );
    }


    /**
    * Возвращаем объект, соответствующий заданному ID
    *
    * @param int ID подкатегории
    * @return Category|false Объект Subcategory object или false, если запись не была найдена или в случае другой ошибки
    */

    public static function getById( $id ) 
    {
        $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
        $sql = "SELECT * FROM subcategory WHERE id = :id";
        $st = $conn->prepare( $sql );
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        $st->execute();
        $row = $st->fetch();
        $conn = null;
        if ($row) 
            return new Subcategory($row);
    }


    /**
    * Возвращаем все (или диапазон) объектов Subategory из базы данных
    *
    * @param int Optional Количество возвращаемых строк (по умолчаниюt = all)
    * @param string Optional Столбец, по которому сортируются категории(по умолчанию = "name ASC")
    * @return Array|false Двух элементный массив: results => массив с объектами Subcategory; totalRows => общее количество подкатегорий
    */
    public static function getList( $numRows=1000000, $order="name ASC" ) 
    {
    $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD);

    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM subcategory
            ORDER BY $order LIMIT :numRows";

    $st = $conn->prepare( $sql );
    $st->bindValue( ":numRows", $numRows, PDO::PARAM_INT );
    $st->execute();
    $list = array();

    while ( $row = $st->fetch() ) {
      $subcategory = new Subcategory( $row );
      $list[] = $subcategory;
    }

    // Получаем общее количество подкатегорий, которые соответствуют критериям
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query( $sql )->fetch();
    $conn = null;
    return ( array ( "results" => $list, "totalRows" => $totalRows[0] ) );
    }


    /**
    * Вставляем текущий объект Subcategory в базу данных и устанавливаем его свойство ID.
    */

    public function insert() {

      // У объекта Subcategory уже есть ID?
      if ( !is_null( $this->id ) ) trigger_error ( "Subcategory::insert(): Attempt to insert a Subcategory object that already has its ID property set (to $this->id).", E_USER_ERROR );

      // Вставляем подкатегорию
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "INSERT INTO subcategory (idCategory, name, description ) VALUES (:idCategory, :name, :description )";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":idCategory", $this->idCategory, PDO::PARAM_INT );
      $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
      $st->bindValue( ":description", $this->description, PDO::PARAM_STR );
      $st->execute();
      $this->id = $conn->lastInsertId();
      $conn = null;
    }


    /**
    * Обновляем текущий объект Subcategory в базе данных.
    */

    public function update() {

      // У объекта Subcategory  есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "Subcategory::update(): Attempt to update a Subcategory object that does not have its ID property set.", E_USER_ERROR );

      // Обновляем подкатегорию
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $sql = "UPDATE subcategory SET idCategory=:idCategory, name=:name, description=:description WHERE id = :id";
      $st = $conn->prepare ( $sql );
      $st->bindValue( ":idCategory", $this->idCategory, PDO::PARAM_INT );
      $st->bindValue( ":name", $this->name, PDO::PARAM_STR );
      $st->bindValue( ":description", $this->description, PDO::PARAM_STR );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }


    /**
    * Удаляем текущий объект Subategory из базы данных.
    */

    public function delete() {

      // У объекта Subategory  есть ID?
      if ( is_null( $this->id ) ) trigger_error ( "Subategory::delete(): Attempt to delete a Subategory object that does not have its ID property set.", E_USER_ERROR );

      // Удаляем подкатегорию
      $conn = new PDO( DB_DSN, DB_USERNAME, DB_PASSWORD );
      $st = $conn->prepare ( "DELETE FROM subcategory WHERE id = :id LIMIT 1" );
      $st->bindValue( ":id", $this->id, PDO::PARAM_INT );
      $st->execute();
      $conn = null;
    }

}
	  