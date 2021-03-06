<?php
session_start();
if(!isset($_SESSION['id'])){
  header("Location:index.php");
}else{
  $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

if(isset($_POST['post-submit'])){
  if(!empty($_POST['posttext'])){
    $posttext = filter_data($_POST['posttext']);
    $result = write_post($posttext, $user_id);
  }
}

if(isset($_POST['del_friends'])){
  remove_friends($user_id, $_POST['del_friends']);
}

if(isset($_POST['post_delete'])){
  $delete_id = $_POST['post_delete'];
  delete_post($delete_id);

}

$post_list = get_friends_and_my_posts($user_id);
$friend_list = get_friend_list($user_id);
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>p42 - Home</title>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/p42_style.css">
</head>

<body>
  <!-- Navigation -->
  <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#p42-navbar" aria-expanded="false">
          <span class="sr-only">Menü anzeigen</span>
          <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
        </button>
        <a class="navbar-brand" href="#">p42</a>
      </div>
      <div class="collapse navbar-collapse" id="p42-navbar">
        <ul class="nav navbar-nav">
          <li class="active"><a href="#">Home</a></li>
          <li><a href="profil.php">Profil</a></li>
          <li><a href="friends.php">Freunde finden</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li><a href="index.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav><!-- /Navigation -->

  <div class="container">
    <div class="row">
      <div class="col-sm-8"> <!-- Hauptinhalt -->

        <!-- Post hinzufügen -->
        <div class="row">
          <div class="col-xs-12">
            <div class="panel panel-default">
              <div class="panel-heading">Was machst du gerade?</div>
              <div class="panel-body">
                <!-- Formulare mit Dateiupload benötigen einen speziellen *enctype*
                http://www.w3schools.com/tags/att_form_enctype.asp -->
                <form enctype="multipart/form-data" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" id="postform">
                  <!-- input-Element, um die User_ID in JavaScript verarbeiten zu können -->
                  <input type="hidden" id="ajax_user_id" value="<?php echo $user_id; ?>" >
                  <fieldset class="form-group">
                    <!-- id="the_text" für AJAX-Requests -->
                    <textarea name="posttext" class="form-control" id="the_text" rows="3"></textarea>
                  </fieldset>
                  <div class="collapse" id="upload_container">
                    <!-- http://getbootstrap.com/components/#wells -->
                    <div class="well">
                      <input type="file" name="post_img" id="post_img">
                    </div>
                  </div>
                  <button class="btn btn-default btn-sm" data-toggle="collapse" href="#upload_container" aria-expanded="false">
                    Bild hinzufügen
                  </button>
                  <button type="submit" id="post-submit" name="post-submit" class="btn btn-primary btn-sm">posten</button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- /Post hinzufügen -->

        <!-- id="posts" für AJAX-Requests -->
        <section id="posts">
          <?php while($post = mysqli_fetch_array($post_list)) {?>
            <!-- Beitrag -->
            <article class="row">
              <div class="col-xs-2">
                <div class="thumbnail p42thumbnail">
                  <!-- http://getbootstrap.com/css/#images -->
                  <img src="user_img/<?php echo $post['img_src']; ?>" alt="Profilbild" class="img-responsive">
                </div><!-- /thumbnail p42thumbnail -->
              </div><!-- /col-sm-2 -->

              <form method="post" class="form-inline" action="<?PHP echo $_SERVER['PHP_SELF'] ?>">
                <input type="hidden" name="post_id" value="<?php echo $user_id; ?>">
                <div class="col-xs-10">
                  <div class="panel panel-info p42panel">
                    <div class="panel-heading">
                      <?php echo $post['firstname'] . " " . $post['lastname']; ?>
                      <?php if($post['owner'] == $user_id){  ?>
                        <!-- Button zum löschen des Posts.
                        Darf nur bei Posts des aktiven Nutzers angezeigt werden. -->
                        <button type="submit" class="close" name="post_delete" value="<?php echo $post['post_id']; ?>">
                          <span aria-hidden="true">&times;</span>
                        </button>
                        <?php
                      } ?>
                    </div>
                    <div class="panel-body">
                      <p><?php echo $post['text'] ?></p>
                      <?php if($post['post_img'] != NULL){  ?>
                        <img src="post_img/<?php echo $post['post_img']; ?>" alt="postimage" class="img-responsive img-thumbnail">
                        <?php
                      } ?>
                    </div>
                    <div class="panel-footer text-right">
                      <button type="submit" name="like_submit" class="btn btn-default btn-xs">
                        <span class="glyphicon glyphicon-thumbs-up text-success" aria-hidden="true"></span>
                        <span class="badge badge-success">2</span>
                      </button>
                      <button type="submit" name="hate_submit" class="btn btn-default btn-xs">
                        <span class="glyphicon glyphicon-thumbs-down text-danger" aria-hidden="true"></span>
                        <span class="badge badge-danger">0</span>
                      </button>
                    </div>
                  </div>
                </div><!-- /col-xs-10 -->
              </form>
            </article> <!-- /Beitrag -->

            <?php
          } ?>

        </section>
      </div> <!-- /Hauptinhalt -->

      <!-- Seitenleiste -->
      <aside class="col-sm-4">

        <!-- Userliste -->
        <form method="post" action="<?PHP echo $_SERVER['PHP_SELF'] ?>" >
          <div class="panel panel-default">
            <div class="panel-heading">Meine Freunde</div>
            <div class="panel-body">
              <?php while($user = mysqli_fetch_assoc($friend_list)) { ?>
                <div class="row">
                  <div class="btn-group col-xs-12" data-toggle="buttons">
                    <label class="btn btn-default btn-block p42-friend-btn">
                      <input type="checkbox" name="del_friends[]" autocomplete="off" value="<?php echo $user['user_id'] ?>" />
                      <span class="glyphicon glyphicon-minus"></span> <?php echo $user['firstname'] . " " . $user['lastname'] ?>
                    </label>
                  </div>
                </div>
                <?php
              } ?>
            </div>
            <div class="panel-footer text-right">
              <div class="row">
                <div class="col-xs-12">
                  <input type="submit" class="btn btn-primary btn-sm" value="aus Freundesliste entfernen" />
                </div>
              </div>
            </div>
          </div>
        </form><!-- /Userliste -->
      </aside> <!-- /Seitenleiste -->

    </div>
  </div> <!-- /container -->

  <!-- jQuery (nötig für alle JavaScript-basierten Plugins von BS) -->
  <script src="js/jquery-3.1.1.min.js"></script>
  <!-- Beinhaltet alle JavaScript-basierten Plugins von BS -->
  <script src="js/bootstrap.min.js"></script>
  <script>

    // Bei Klick auf den "posten"-Button
    // Absenden des Formulars unterbinden
    // User_ID auslesen

    // Posttext aus der Textarea auslesen
    // Sicherheitsabfrage, damit keine leeren Posts erzeugt werden.
      // Text in Textarea löschen

      // Initialisierung eines AJAX-Requests
        // Adresse des Skripts
        // Sendemethode der Daten : POST
        // zu sendenden Daten für user_id und posttext in data sammeln
        // als Datentyp kommt html zurück


      // Wenn der Request Erfolg hatte
        // empfangenen Text als HTML parsen
        // html an den Anfang von #posts einfügen und einblenden



        // Aktion, wenn ein Fehler auftritt.
      

  </script>

</body>
</html>
