<?php

require("config.php");
session_start();
$action = isset($_GET['action']) ? $_GET['action'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";

if ($action != "login" && $action != "logout" && !$username) {
    login();
    exit;
}

switch ($action) {
// Авторизация
    case 'login':
        login();
        break;
    case 'logout':
        logout();
        break;
// Статьи
    case 'newArticle':
        newArticle();
        break;
    case 'editArticle':
        editArticle();
        break;
    case 'deleteArticle':
        deleteArticle();
        break;
// Категории
    case 'listCategories':
        listCategories();
        break;
    case 'newCategory':
        newCategory();
        break;
    case 'editCategory':
        editCategory();
        break;
    case 'deleteCategory':
        deleteCategory();
        break;
// Подкатегории
    case 'listSubcategories':
        listSubcategories();
       break;
    case 'newSubcategory':
        newSubcategory();
        break;
    case 'editSubcategory':
        editSubcategory();
        break;
    case 'deleteSubcategory':
        deleteSubcategory();
        break;
// Пользователи
    case 'listUsers':
        listUsers();
        break;
    case 'newUser':
        newUser();
        break;
    case 'editUser':
        editUser();
        break;
    case 'deleteUser':
        deleteUser();
        break;    
    default:
        listArticles();
}

/**
 * Авторизация пользователя (админа) -- установка значения в сессию
 */
function login() {

    $results = array();
    $results['pageTitle'] = "Admin Login | Widget News";

    if (isset($_POST['login'])) {

        // Получаем форму входа. Если логин 'admin' - сверяем пароль администратора
        if ($_POST['username'] == ADMIN_USERNAME) {
            if ($_POST['password'] == ADMIN_PASSWORD) {
                $_SESSION['username'] = ADMIN_USERNAME;
                header( "Location: admin.php");
            } else {
                $results['errorMessage'] = "Неправильный логин или пароль! Попробуйте войти ещё раз.";
                require( TEMPLATE_PATH . "/admin/loginForm.php" );
            }
        } 
        //Если не админ, проверяем логин и пароль из базы данных
        elseif($_POST['username'] != ADMIN_USERNAME ){
            
            $conn = new PDO(DB_DSN, DB_USERNAME, DB_PASSWORD);
            $sql = "SELECT * FROM users WHERE username = :username";
            $st = $conn->prepare($sql);
            $st->bindValue(":username", $_POST['username'], PDO::PARAM_STR);
            $st->execute();
            $row = $st->fetch();
            $conn = null;
            $user = new User($row);

            if (($_POST['username'] == $user->username) && ($_POST['password'] == $user->password)){
                
                if ($user->activeUser != 1) {
                    $results['errorMessage'] = "Извините, доступ запрещён!";
                    require( TEMPLATE_PATH . "/admin/loginForm.php" );
                } else {
                    $_SESSION['username'] = $user->username;
                    header( "Location: admin.php");
                } 
            }
            else {
                // Ошибка входа: выводим сообщение об ошибке для пользователя
                $results['errorMessage'] = "Неправильный логин или пароль! Попробуйте войти ещё раз.";
                require( TEMPLATE_PATH . "/admin/loginForm.php" );
            }
        }
    } else {
      // Пользователь еще не получил форму: выводим форму
      require(TEMPLATE_PATH . "/admin/loginForm.php");
    }
}

/**
 * Выход из админки
 */
function logout() {
    unset( $_SESSION['username'] );
    header( "Location: admin.php" );
}


function newArticle() {
	  
    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if ( isset( $_POST['saveChanges'] ) ) {
//            echo "<pre>";
//            print_r($results);
//            print_r($_POST);
//            echo "<pre>";
//            В $_POST данные о статье сохраняются корректно
        // Пользователь получает форму редактирования статьи: сохраняем новую статью
        $article = new Article();
        $article->storeFormValues( $_POST );

        // Проверка на соответствие связи (категория - подкатегория)
        $rows = Article::checkSubcategory($_POST['categoryId'], $_POST['subcategoryId']);
        if ( $rows[0] < 1 ) {
            header( "Location: admin.php?action=listSubcategories&error=subcategoryNotMatch" );
            return;
        }
        
//            echo "<pre>";
//            print_r($article);
//            echo "<pre>";
//            А здесь данные массива $article уже неполные(есть только Число от даты, категория и полный текст статьи)          
        $article->insert();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь сбросил результаты редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользователь еще не получил форму редактирования: выводим форму
        $results['article'] = new Article;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        $data = Subcategory::getList();
        $results['subcategories'] = $data['results'];
        $data = User::getList();
        $results['authors'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editArticle.php" );
    }
}


/**
 * Редактирование статьи
 * 
 * @return null
 */
function editArticle() {
	  
    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if (isset($_POST['saveChanges'])) {

        // Пользователь получил форму редактирования статьи: сохраняем изменения
        if ( !$article = Article::getById( (int)$_POST['articleId'] ) ) {
            header( "Location: admin.php?error=articleNotFound" );
            return;
        }

        // Проверка на соответствие связи (категория - подкатегория)
        $rows = Article::checkSubcategory($_POST['categoryId'], $_POST['subcategoryId']);
        if ( $rows[0] < 1 ) {
            header( "Location: admin.php?action=listSubcategories&error=subcategoryNotMatch" );
            return;
        }

        $article->authors = [];
        $article->storeFormValues( $_POST );
        $article->update();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // Пользователь отказался от результатов редактирования: возвращаемся к списку статей
        header( "Location: admin.php" );
    } else {

        // Пользвоатель еще не получил форму редактирования: выводим форму
        $results['article'] = Article::getById((int)$_GET['articleId']);
        $data = Category::getList();
        $results['categories'] = $data['results'];
        $data = Subcategory::getList();
        $results['subcategories'] = $data['results'];
        $data = User::getList();
        $results['authors'] = $data['results'];
        require(TEMPLATE_PATH . "/admin/editArticle.php");
    }

}


function deleteArticle() {

    if ( !$article = Article::getById( (int)$_GET['articleId'] ) ) {
        header( "Location: admin.php?error=articleNotFound" );
        return;
    }

    $article->delete();
    header( "Location: admin.php?status=articleDeleted" );
}


function listArticles() {
    $results = array();
    
    $data = Article::getList();
    $results['articles'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) { 
        $results['categories'][$category->id] = $category;
    }

    $data = Subcategory::getList();
    $results['subcategories'] = array();
    foreach ($data['results'] as $subcategory) { 
        $results['subcategories'][$subcategory->id] = $subcategory;
    }
    
    $data = User::getList();
    $results['authors'] = $data['results'];
    
    $results['pageTitle'] = "Все статьи";

    if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
        if ($_GET['error'] == "articleNotFound") 
            $results['errorMessage'] = "Error: Article not found.";
    }

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "articleDeleted")  {
            $results['statusMessage'] = "Article deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listArticles.php" );
}

function listCategories() {
    $results = array();
    $data = Category::getList();
    $results['categories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Categories";

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "categoryNotFound" ) $results['errorMessage'] = "Error: Category not found.";
        if ( $_GET['error'] == "categoryContainsArticles" ) $results['errorMessage'] = "Error: Category contains articles. Delete the articles, or assign them to another category, before deleting this category.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "categoryDeleted" ) $results['statusMessage'] = "Category deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listCategories.php" );
}
	  
	  
function newCategory() {

    $results = array();
    $results['pageTitle'] = "New Article Category";
    $results['formAction'] = "newCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the new category
        $category = new Category;
        $category->storeFormValues( $_POST );
        $category->insert();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = new Category;
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function editCategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Category";
    $results['formAction'] = "editCategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the category edit form: save the category changes

        if ( !$category = Category::getById( (int)$_POST['categoryId'] ) ) {
          header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
          return;
        }

        $category->storeFormValues( $_POST );
        $category->update();
        header( "Location: admin.php?action=listCategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the category list
        header( "Location: admin.php?action=listCategories" );
    } else {

        // User has not posted the category edit form yet: display the form
        $results['category'] = Category::getById( (int)$_GET['categoryId'] );
        require( TEMPLATE_PATH . "/admin/editCategory.php" );
    }

}


function deleteCategory() {

    if ( !$category = Category::getById( (int)$_GET['categoryId'] ) ) {
        header( "Location: admin.php?action=listCategories&error=categoryNotFound" );
        return;
    }

    $articles = Article::getList( 1000000, $category->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listCategories&error=categoryContainsArticles" );
        return;
    }

    $category->delete();
    header( "Location: admin.php?action=listCategories&status=categoryDeleted" );
}

/**
 * Создание пользователя
 * 
 * @return null
 */
function newUser() {
	  
    $results = array();
    $results['pageTitle'] = "New User";
    $results['formAction'] = "newUser";

    if ( isset( $_POST['saveChanges'] ) ) {

        $user = new User;
        $user->storeFormValues( $_POST );
        $user->insert();
        header( "Location: admin.php?action=listUsers&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        header( "Location: admin.php?action=listUsers" );
    } else {

        $results['user'] = new User;
        require( TEMPLATE_PATH . "/admin/editUser.php" );
    }
}


/**
 * Редактирование пользователя
 * 
 * @return null
 */
function editUser() {
	  
    $results = array();
    $results['pageTitle'] = "Edit User";
    $results['formAction'] = "editUser";

    if (isset($_POST['saveChanges'])) {

        if ( !$user = User::getById( (int)$_POST['userId'] ) ) {
            header( "Location: admin.php?error=userNotFound" );
            return;
        }

        $user->storeFormValues( $_POST );
        $user->update();
        header( "Location: admin.php?status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {
        header( "Location: admin.php" );
    } else {
        $results['user'] = User::getById((int)$_GET['userId']);
        require(TEMPLATE_PATH . "/admin/editUser.php");
    }

}

/**
 * Удаление пользователя
 * 
 * @return null
 */
function deleteUser() {

    if ( !$user = User::getById( (int)$_GET['userId'] ) ) {
        header( "Location: admin.php?error=userNotFound" );
        return;
    }

    $user->delete();
    header( "Location: admin.php?status=userDeleted" );
}

/**
 * Просмотр пользователей
 * 
 * @return null
 */
function listUsers() {
    $results = array();
    
    $data = User::getList();
    $results['users'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    
    $results['pageTitle'] = "Все пользователи";

    if (isset($_GET['error'])) { // вывод сообщения об ошибке (если есть)
        if ($_GET['error'] == "userNotFound") 
            $results['errorMessage'] = "Error: User not found.";
    }

    if (isset($_GET['status'])) { // вывод сообщения (если есть)
        if ($_GET['status'] == "changesSaved") {
            $results['statusMessage'] = "Your changes have been saved.";
        }
        if ($_GET['status'] == "userDeleted")  {
            $results['statusMessage'] = "User deleted.";
        }
    }

    require(TEMPLATE_PATH . "/admin/listUsers.php" );
}

/**
 * Просмотр подкатегорий
 */
function listSubcategories() {
    $results = array();
    $data = Subcategory::getList();
    $results['subcategories'] = $data['results'];
    $results['totalRows'] = $data['totalRows'];
    $results['pageTitle'] = "Article Subategories";

    $data = Category::getList();
    $results['categories'] = array();
    foreach ($data['results'] as $category) { 
        $results['categories'][$category->id] = $category;
    }

    if ( isset( $_GET['error'] ) ) {
        if ( $_GET['error'] == "subcategoryNotFound" ) $results['errorMessage'] = "Error: Subcategory not found.";
        if ( $_GET['error'] == "subcategoryNotMatch" ) $results['errorMessage'] = "Error: Subcategory does not match category";
        if ( $_GET['error'] == "subcategoryContainsArticles" ) $results['errorMessage'] = "Error: Subcategory contains articles. Delete the articles, or assign them to another Subcategory, before deleting this Subcategory.";
    }

    if ( isset( $_GET['status'] ) ) {
        if ( $_GET['status'] == "changesSaved" ) $results['statusMessage'] = "Your changes have been saved.";
        if ( $_GET['status'] == "subcategoryDeleted" ) $results['statusMessage'] = "Subcategory deleted.";
    }

    require( TEMPLATE_PATH . "/admin/listSubcategories.php" );
}
	  
/**
 * Создание подкатегории
 */  
function newSubcategory() {

    $results = array();
    $results['pageTitle'] = "New Article Subcategory";
    $results['formAction'] = "newSubcategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the subcategory edit form: save the new subcategory
        $subcategory = new Subcategory;
        $subcategory->storeFormValues( $_POST );
        $subcategory->insert();
        header( "Location: admin.php?action=listSubcategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the subcategory list
        header( "Location: admin.php?action=listSubategories" );
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = new Subcategory;
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editSubcategory.php" );
    }

}

/**
 * Изменение подкатегории
 */
function editSubcategory() {

    $results = array();
    $results['pageTitle'] = "Edit Article Subcategory";
    $results['formAction'] = "editSubcategory";

    if ( isset( $_POST['saveChanges'] ) ) {

        // User has posted the Subcategory edit form: save the Subcategory changes

        if ( !$subcategory = Subcategory::getById( (int)$_POST['subcategoryId'] ) ) {
          header( "Location: admin.php?action=listSubcategories&error=subcategoryNotFound" );
          return;
        }

        $subcategory->storeFormValues( $_POST );
        $subcategory->update();
        header( "Location: admin.php?action=listSubcategories&status=changesSaved" );

    } elseif ( isset( $_POST['cancel'] ) ) {

        // User has cancelled their edits: return to the subcategory list
        header( "Location: admin.php?action=listSubcategories" );
    } else {

        // User has not posted the subcategory edit form yet: display the form
        $results['subcategory'] = Subcategory::getById( (int)$_GET['subcategoryId'] );
        $data = Category::getList();
        $results['categories'] = $data['results'];
        require( TEMPLATE_PATH . "/admin/editSubcategory.php" );
    }

}

/**
 * Удаление подкатегории
 */
function deleteSubcategory() {

    if ( !$subcategory = Subcategory::getById( (int)$_GET['subcategoryId'] ) ) {
        header( "Location: admin.php?action=listSubcategories&error=subcategoryNotFound" );
        return;
    }

    $articles = Article::getList( 1000000, $subcategory->id );

    if ( $articles['totalRows'] > 0 ) {
        header( "Location: admin.php?action=listSubcategories&error=subcategoryContainsArticles" );
        return;
    }

    $subcategory->delete();
    header( "Location: admin.php?action=listSubcategories&status=subcategoryDeleted" );
}