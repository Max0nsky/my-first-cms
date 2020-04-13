<?php if ($_SESSION['username'] !== 'admin') {
    header("Location: admin.php");
}?>
<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<!--        <?php echo "<pre>";
            print_r($results);
            print_r($data);
        echo "<pre>"; ?> Данные о массиве $results и типе формы передаются корректно-->

        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
            <input type="hidden" name="userId" value="<?php echo $results['user']->id ?>">

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

            <ul>

              <li>
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Username" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['user']->username )?>" />
              </li>

              <li>
                <label for="password">Password</label>
                <input type="text" name="password" id="password" placeholder="Password" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['user']->password )?>" />
              </li>

              <li>
                  <label for="checkActivity">Active</label>
                  <input type="checkbox" name="activeUser" value="1" id="checkboxActivity"
                  <?php
                        if($results['user']->activeUser == 1) {
                            echo 'checked = "checked"';
                        }
                  ?>>
              </li>

            </ul>

            <div class="buttons">
              <input type="submit" name="saveChanges" value="Save Changes" />
              <input type="submit" formnovalidate name="cancel" value="Cancel" />
            </div>

        </form>

    <?php if ($results['user']->id) { ?>
          <p><a href="admin.php?action=deleteUser&amp;userId=<?php echo $results['user']->id ?>" onclick="return confirm('Delete This User?')">
                  Delete This User
              </a>
          </p>
    <?php } ?>
	  
<?php include "templates/include/footer.php" ?>

          