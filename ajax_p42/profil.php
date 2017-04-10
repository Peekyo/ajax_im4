<?php
session_start();
if(!isset($_SESSION['id'])){
  header("Location:index.php");
}else{
  $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

if(isset($_POST['update-submit']))
{
  $email = filter_data($_POST['email']);
  $password = filter_data($_POST['password']);
  $confirm_password = filter_data($_POST['confirm-password']);
  $gender = filter_data($_POST['gender']);
  $firstname = filter_data($_POST['firstname']);
  $lastname = filter_data($_POST['lastname']);
  $image_name = "";

  $result = get_user($user_id);
  $user = mysqli_fetch_assoc($result);
  $image_name = $user['img_src'];

  $upload_path = "user_img/";
  $max_file_size = 500000;
  $upload_ok = true;

  if ($_FILES['profil_img']['name'] != "") {
    $filetype = $_FILES['profil_img']['type'];
    switch($filetype){
      case "image/jpg":
      $file_extension = "jpg";
      break;
      case "image/jpeg":
      $file_extension = "jpg";
      break;
      case "image/gif":
      $file_extension = "gif";
      break;
      case "image/png":
      $file_extension = "png";
      break;
      default:
      $upload_ok = false;
    }

    $upload_filesize = $_FILES['profil_img']['size'];
    if($upload_filesize >= $max_file_size){
      $upload_ok = false;
      echo "Leider ist die Datei mit $upload_filesize KB zu gross. <br> Sie darf nicht grösser als $max_file_size sein. ";
    }

    if($upload_ok){
      $image_name = time() . "_" . $user['user_id'] . "." . $file_extension;
      move_uploaded_file($_FILES['profil_img']['tmp_name'], $upload_path . $image_name);
    }else{
      echo "Leider konnte die Datei nicht hochgeladen werden. ";
    }
  }
  $result = update_user($user_id, $email, $password, $confirm_password, $gender, $firstname, $lastname, $image_name);
}


$result = get_user($user_id);
$user = mysqli_fetch_assoc($result);

$update_time = date_parse($user['update_time']);
$last_update = $update_time['day'] . "." . $update_time['month'] . "." . $update_time['year'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Die drei vorausgehenden meta-Tags *müssen* vor allen anderen Inhalten des head stehen -->
  <title>p42 - Profil</title>
  <!-- Bootstrap Styles -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- eigene Styles -->
  <link rel="stylesheet" href="css/p42_style.css">
</head>
<body>
  <!-- Navigation -->
  <!-- http://getbootstrap.com/components/#navbar -->
  <nav class="navbar navbar-default navbar-fixed-top">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#p42-navbar" aria-expanded="false">
          <!-- Übersetzung für Screenreader-Text -->
          <span class="sr-only">Menü anzeigen</span>
          <!-- Wir ersetzen drei waagerechte Striche (Burgermenü) durch Glyphicon -->
          <span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
        </button>
        <a class="navbar-brand" href="#">p42</a>
      </div>
      <!-- Sichtbarer Inhalt des Menüs -->
      <div class="collapse navbar-collapse" id="p42-navbar">
        <ul class="nav navbar-nav">
          <li><a href="home.php">Home</a></li>
          <!-- Der Menüpunkt der aktuellen Seite ist mit class="active" markiert und ist nicht verlinkt -->
          <li class="active"><a href="#">Profil</a></li>
          <li><a href="friends.php">Freunde finden</a></li>
        </ul>
        <!-- rechtsbündiger Inhalt -->
        <ul class="nav navbar-nav navbar-right">
          <li><a href="index.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav><!-- /Navigation -->

  <div class="container">
    <div class="panel panel-default container-fluid"> <!-- fluid -->
      <div class="panel-heading row">
        <div class="col-sm-6">
          <h4>Persönliche Einstellungen</h4>
        </div>
        <!-- Button für die Einblendung des modalen Fensters zur Userdatenaktualisierung -->
        <div class="col-xs-6 text-right">
          <button type="button" class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#myModal">Profil anpassen</button>
        </div>
        <!-- /Button Userdatenaktualisierung -->
      </div>
      <div class="panel-body">
        <div class="col-sm-3">
          <!-- /Profilbild -->
          <img src="user_img/<?php echo $user['img_src']; ?>" alt="Profilbild" class="img-responsive">
          <!-- Profilbild -->
        </div>
        <div class="col-sm-9">
          <!-- Profildaten des Users -->
          <dl class="dl-horizontal lead">
            <dt>Name</dt>
            <dd><?php echo $user['firstname'] . " " . $user['lastname']; ?></dd>

            <!--<dt>Nutzername</dt>
            <dd>wobo</dd>-->

            <dt>E-Mail</dt>
            <dd><?php echo $user['email']; ?></dd>

            <dt>letzte Änderung</dt>
            <dd>Ihr Profil wurde zuletzt am <?php echo $last_update; ?> aktualisiert.</dd>
          </dl>
          <!-- / Profildaten des Users -->
        </div>
      </div>
    </div>

    <!-- Modales Fenster zur Userdatenaktualisierung-->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="p42-profil-modalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="modal-header">
              <h4 class="modal-title" id="p42-profil-modalLabel">persönliche Einstellungen</h4>
            </div>
            <div class="modal-body">
              <div class="form-group row">
                <label for="Gender" class="col-sm-2 form-control-label">Anrede</label>
                <div class="col-sm-5">
                  <select class="form-control form-control-sm" id="Gender" name="gender">
                    <option <?php if($user['gender'] == "") echo "selected"; ?> value="">--</option>
                    <option <?php if($user['gender'] == "Frau") echo "selected"; ?> value="Frau">Frau</option>
                    <option <?php if($user['gender'] == "Herr") echo "selected"; ?> value="Herr">Herr</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="Vorname" class="col-sm-2 col-xs-12 form-control-label">Name</label>
                <div class="col-sm-5 col-xs-6">
                  <input  type="text" class="form-control form-control-sm"
                  id="Vorname" placeholder="Vorname"
                  name="firstname" value="<?php echo $user['firstname']; ?>">
                </div>
                <div class="col-sm-5 col-xs-6">
                  <input  type="text" class="form-control form-control-sm"
                  id="Nachname" placeholder="Nachname"
                  name="lastname" value="<?php echo $user['lastname']; ?>">
                </div>
              </div>
              <div class="form-group row">
                <label for="Email" class="col-sm-2 form-control-label">E-Mail</label>
                <div class="col-sm-10">
                  <input  type="email" class="form-control form-control-sm"
                  id="Email" placeholder="E-Mail" required
                  name="email" value="<?php echo $user['email']; ?>">
                </div>
              </div>
              <div class="form-group row">
                <label for="Passwort" class="col-sm-2 form-control-label">Password</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control form-control-sm" id="Passwort" placeholder="Passwort" name="password">
                </div>
              </div>
              <div class="form-group row">
                <label for="Passwort_Conf" class="col-sm-2 form-control-label">Passwort bestätigen</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control form-control-sm" id="Passwort_Conf" placeholder="Passwort" name="confirm-password">
                </div>
              </div>

              <div class="form-group row">
                <!-- http://plugins.krajee.com/file-input -->
                <label for="Tel" class="col-sm-2 form-control-label">Profilbild</label>
                <div class="col-sm-10">
                  <input type="file" name="profil_img">
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Abbrechen</button>
              <button type="submit" class="btn btn-success btn-sm" name="update-submit">Änderungen speichern</button>
            </div>
          </form>

        </div>
      </div>
    </div>
    <!-- /Modales Fenster zur Userdatenaktualisierung-->

  </div>

  <!-- jQuery (nötig für alle JavaScript-basierten Plugins von BS) -->
  <script src="js/jquery-3.1.1.min.js"></script>
  <!-- Beinhaltet alle JavaScript-basierten Plugins von BS -->
  <script src="js/bootstrap.min.js"></script>

</body>
</html>
