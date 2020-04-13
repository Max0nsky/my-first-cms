<?php


/**
 * Класс для работы с пользователями
 */
class User
{
  // Свойства
  /**
   * @var int ID пользователя из базы данны
   */
  public $id = null;

  /**
   * @var string Имя пользователя
   */
  public $username = null;

  /**
   * @var string Пароль пользователя
   */
  public $password = null;

  /**
   * @var string Активен ли пользователь
   */
  public $activeUser = null;

  /**
   * Устанавливаем свойства с помощью значений в заданном массиве
   *
   * @param assoc Значения свойств
   */


  /**
   * Создаст объект пользователя
   * 
   * @param array $data массив значений (столбцов) строки таблицы пользователей
   */
  public function __construct($data = array())
  {

    if (isset($data['id'])) {
      $this->id = (int) $data['id'];
    }

    if (isset($data['username'])) {
      $this->username = $data['username'];
    }

    if (isset($data['password'])) {
      $this->password = $data['password'];
    }

    if (isset($data['activeUser'])) {
      $this->activeUser = $data['activeUser'];
    } else {
      $this->activeUser = 0;
    }
  }

  /**
   * Устанавливаем свойства с помощью значений формы редактирования  в заданном массиве
   *
   * @param assoc Значения формы
   */
  /*public function storeFormValues ( $params ) {

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
    }*/


  /**
   * Устанавливаем свойства с помощью значений формы редактирования записи в заданном массиве
   *
   * @param assoc Значения записи формы
   */
  public function storeFormValues($params)
  {

    // Сохраняем все параметры
    $this->__construct($params);
  }

  /**
   * Возвращаем объект пользователя соответствующий заданному ID
   *
   * @param int ID пользователя
   * @return User|false Объект пользователя или false, если запись не найдена или возникли проблемы
   */
  public static function getById($id)
  {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "SELECT * FROM users WHERE id = :id";
    $st = $conn->prepare($sql);
    $st->bindValue(":id", $id, PDO::PARAM_INT);
    $st->execute();

    $row = $st->fetch();
    $conn = null;

    if ($row) {
      return new User($row);
    }
  }


  /**
   * Возвращает все (или диапазон) объекты User из базы данных
   *
   * @param int $numRows Количество возвращаемых строк (по умолчанию = 1000000)
   * @param string $order Столбец, по которому выполняется сортировка статей (по умолчанию = "publicationDate DESC")
   * @return Array|false Двух элементный массив: results => массив объектов Article; totalRows => общее количество строк
   */
  public static function getList($numRows = 1000000, $order = "id DESC")
  {
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);

    $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM users ORDER BY  $order  LIMIT :numRows";

    $st = $conn->prepare($sql);
    //                        echo "<pre>";
    //                        print_r($st);
    //                        echo "</pre>";
    //                        Здесь $st - текст предполагаемого SQL-запроса, причём переменные не отображаются
    $st->bindValue(":numRows", $numRows, PDO::PARAM_INT);

    $st->execute(); // выполняем запрос к базе данных
    //                        echo "<pre>";
    //                        print_r($st);
    //                        echo "</pre>";
    //                        Здесь $st - текст предполагаемого SQL-запроса, причём переменные не отображаются
    $list = array();

    while ($row = $st->fetch()) {
      $user = new User($row);
      $list[] = $user;
    }

    // Получаем общее количество пользователей, которые соответствуют критерию
    $sql = "SELECT FOUND_ROWS() AS totalRows";
    $totalRows = $conn->query($sql)->fetch();
    $conn = null;

    return (array(
      "results" => $list,
      "totalRows" => $totalRows[0]
    ));
  }


  /**
   * Вставляем текущий объект статьи в базу данных, устанавливаем его свойства.
   */


  /**
   * Вставляем текущий объект User в базу данных, устанавливаем его ID.
   */
  public function insert()
  {

    // Есть уже у объекта User ID?
    if (!is_null($this->id)) trigger_error("User::insert(): Attempt to insert an User object that already has its ID property set (to $this->id).", E_USER_ERROR);

    // Добавляем пользователя
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);

    $sql = "INSERT INTO users (username, password, activeUser )  
        VALUES (:username, :password, :activeUser)";
    $st = $conn->prepare($sql);
    $st->bindValue(":username", $this->username, PDO::PARAM_STR);
    $st->bindValue(":password", $this->password, PDO::PARAM_STR);
    $st->bindValue(":activeUser", $this->activeUser, PDO::PARAM_INT);
    $st->execute();
    $this->id = $conn->lastInsertId();
    $conn = null;
  }

  /**
   * Обновляем текущего пользователя в базе данных
   */
  public function update()
  {

    // Есть ли у объекта ID?
    if (is_null($this->id)) trigger_error("User::update(): "
      . "Attempt to update an User object "
      . "that does not have its ID property set.", E_USER_ERROR);

    // Обновляем данные пользователя
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $sql = "UPDATE users SET username=:username, password=:password, activeUser=:activeUser WHERE id = :id";
    $st = $conn->prepare($sql);
    $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    $st->bindValue(":username", $this->username, PDO::PARAM_STR);
    $st->bindValue(":password", $this->password, PDO::PARAM_STR);
    $st->bindValue(":activeUser", $this->activeUser, PDO::PARAM_INT);
    $st->execute();
    $conn = null;
  }


  /**
   * Удаляем текущего ползователя из базы данных
   */
  public function delete()
  {

    // Есть ли у объекта ID?
    if (is_null($this->id)) trigger_error("User::delete(): Attempt to delete an User object that does not have its ID property set.", E_USER_ERROR);

    // Удаляем пользователя
    $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
    $st = $conn->prepare("DELETE FROM users WHERE id = :id LIMIT 1");
    $st->bindValue(":id", $this->id, PDO::PARAM_INT);
    $st->execute();
    $conn = null;
  }
}
