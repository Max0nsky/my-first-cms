<?php if ($_SESSION['username'] !== 'admin') {
    header("Location: admin.php");
}?>
<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
	  
    <h1>All Users</h1>

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>


    <?php if ( isset( $results['statusMessage'] ) ) { ?>
            <div class="statusMessage"><?php echo $results['statusMessage'] ?></div>
    <?php } ?>

          <table>
            <tr>
              <th>Id</th>
              <th>Username</th>
              <th>Active</th>
            </tr>
            
    <?php foreach ( $results['users'] as $user ) { ?>

            <tr onclick="location='admin.php?action=editUser&amp;userId=<?php echo $user->id?>'">
              <td>
                <?php echo $user->id?>
              </td>
              <td>
                <?php echo $user->username?>
              </td>
              <td>
                  <?php
                    if($user->activeUser) {
                        echo 1;
                    } else {
                        echo 0;
                    }
                  ?>
              </td>
            </tr>

    <?php } ?>

          </table>

          <p><?php echo $results['totalRows']?> user<?php echo ( $results['totalRows'] != 1 ) ? 's' : '' ?> in total.</p>

          <p><a href="admin.php?action=newUser">Add a New User</a></p>

<?php include "templates/include/footer.php" ?>              