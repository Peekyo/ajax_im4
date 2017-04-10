<?php
session_start();
if(!isset($_SESSION['id'])){
  header("Location:index.php");
}else{
  $user_id = $_SESSION['id'];
}

require_once('system/data.php');
require_once('system/security.php');

if(isset($_POST['new_friends'])){
  add_friends($user_id, $_POST['new_friends']);
}

if(isset($_POST['del_friends'])){
  remove_friends($user_id, $_POST['del_friends']);
}

$no_friend_list = get_no_friend_list($user_id);
$friend_list = get_friend_list($user_id);


?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>p42 - Freunde finden</title>
  <!-- Bootstrap Styles -->
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <!-- eigene Styles -->
  <link rel="stylesheet" href="css/p42_style.css">
</head>
<body>
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
          <li><a href="home.php">Home</a></li>
          <li><a href="profil.php">Profil</a></li>
          <li class="active"><a href="#">Freunde finden</a></li>
        </ul>
        <ul class="nav navbar-nav navbar-right">
          <li><a href="index.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Logout</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav><!-- /Navigation -->

  <div class="container">

    <div class="page-header">
      <h1>alle p42-ler, die noch nicht meine Freunde sind</h1>
    </div>
    <div class="row">
      <div class="col-sm-8"> <!-- Hauptinhalt -->
        <div class="row">
          <div class="panel panel-default">
            <div class="panel-body">
              <div class="col-xs-12">
                <form method="post" action="friends.php" id="searchform">
                  <!-- http://getbootstrap.com/components/#input-groups-buttons -->
                  <div class="input-group">
                    <input type="text" class="form-control" placeholder="Wen suchst du ...">
                    <span class="input-group-btn">
                      <button type="submit" name="search-submit" class="btn btn-primary">
                        <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
                      </button>
                    </span>
                  </div><!-- /input-group -->
                </form>
              </div><!-- /.col-xs-12 -->


              <form method="post" action="friends.php" id="new_friend_form">
                <!-- input-Element, um die User_ID in JavaScript verarbeiten zu können -->
                <input type="hidden" id="ajax_user_id" value="<?php echo $user_id; ?>" >

                  <div id="no_friend_list">
                    <?php while($user = mysqli_fetch_assoc($no_friend_list)) { ?>

                    <!-- Freund+ Button -->
                      <!-- Die Klasse not_my_friend für AJAX-Requests -->
                      <div class="btn-group col-xs-12 not_my_friend" data-toggle="buttons" >
                        <label class="btn btn-default btn-block p42-friend-btn">
                          <input type="checkbox" name="new_friends[]" autocomplete="off" value="<?php echo $user['user_id']; ?>" >
                          <span class="glyphicon glyphicon-plus"></span> <?php echo $user['firstname'] . " " . $user['lastname']; ?>
                        </label>
                      </div>
                      <!-- /Freund+ Button -->

                    <?php } ?>
                  </div>

                  <div class="btn-group col-xs-12 p42-friend-btn">
                    <input type="submit" name="new_friend_btn" id="new_friend_btn" class="btn btn-primary btn-sm" value="zu meinen Freunden hinzufügen" />
                  </div>
                </form>

              </div>
            </div>
          </div>
        </div> <!-- /Hauptinhalt -->

        <!-- Seitenleiste -->
        <aside class="col-sm-4">
          <!-- Userliste -->

          <form method="post" action="<?PHP echo $_SERVER['PHP_SELF'] ?>" >
            <div class="panel panel-default">
              <div class="panel-heading">Meine Freunde</div>
              <div class="panel-body" id="friend_list">
                <?php while($user = mysqli_fetch_assoc($friend_list)) { ?>
                  <!-- Die Klasse my_friend für AJAX-Requests -->
                  <div class="row my_friend" >
                    <div class="btn-group col-xs-12" data-toggle="buttons">
                      <label class="btn btn-default btn-block p42-friend-btn">
                        <input type="checkbox" name="del_friends[]" autocomplete="off" value="<?php echo $user['user_id'] ?>" />
                        <span class="glyphicon glyphicon-minus"></span> <?php echo $user['firstname'] . " " . $user['lastname'] ?>
                      </label>
                    </div>
                  </div>

                  <?php } ?>
                </div>

                <div class="panel-footer text-right">
                  <div class="row">
                    <div class="col-xs-12">
                      <input type="submit" id="de_friend_btn" class="btn btn-primary btn-sm" value="aus Freundesliste entfernen" />
                    </div>
                  </div>
                </div>
              </div>

            </form><!-- /Userliste -->
          </aside> <!-- /Seitenleiste -->
      </div>
    </div>

    <!-- jQuery (nötig für alle JavaScript-basierten Plugins von BS) -->
    <script src="js/jquery-3.1.1.min.js"></script>
    <!-- Beinhaltet alle JavaScript-basierten Plugins von BS -->
    <script src="js/bootstrap.min.js"></script>

    <script>
    /* sichtbare Änderungen der Seiten */
    //einblenden des Elements
    function change_view(hide_element, show_element, parent_element_id){  // hide_element = das geklickte Element
      // Es wird innerhalb von 300ms verborgen und ...
      $(hide_element).hide( 300, function() {
        // ... danach entfernt.
        $(hide_element).remove();
      });

      html = $.parseHTML(show_element);
      // Der versteckte Code wird als letztes Element innerhalb des Elements mit id=parent_element_id
      //(Funktionsparameter) angehängt und innerhalb von 300ms eingeblendet.
      $(html).hide().appendTo(parent_element_id).show(300);
      $(html).click(function() { // Dem neuen Element wird ein EventHandler (click) angehängt.
        execute_change( $(this) );
      });
    };

  function execute_change(element){ //Element = worauf der User klickt
      var u_id = $("#ajax_user_id").attr("value"); //Die AJAX user id hat ein Attribut value-->Zeile:87
      //Person auf welche geklickt wurde
      var f_id = $(element).find("input").attr("value"); //Finde ein Input Element + ruft den Wert vom attr ab
      if ($(element).hasClass("not_my_friend")) {
        //Objekt erzeugen
        var send_data = {user_id : u_id, new_friend : f_id}; //Bei Objekten stehen : für =
        var parent_element_id = "#friend_list"; //gibt an wohin der neue Freund geschrieben werden soll
        //Freund löschen
      }else if ($(element).hasClass("my_friend")) {
        //Objekt erzeugen + del_friend!
        var send_data = {user_id : u_id, del_friend : f_id}; //Bei Objekten stehen : für =
        var parent_element_id = "#no_friend_list"; //gibt an wohin der neue Freund geschrieben werden soll
      }
      //AJAX request erzeugen
      var request = $.ajax({
          url : "ajax/ajax_friends.php",  //dorthin werden die Daten geschickt
          method : "GET", //Methode festlegen
          data : send_data, //welche Daten übertragen werden
          dataType : "html", //was für Daten zurück kommen
          success : function(data_from_script){ //gibt an was im Erfolgsfall(200) passiert
            change_view($(element), data_from_script, parent_element_id);
          }
      })
    }
    /* es muss etwas passieren wenn User auf friend hinzufügen klickt */
    $(".not_my_friend").click(function() {
      execute_change($(this)); /* Funktion execute change wird aufgerufen*/
    })
    //Eventhandler Freunde entfernen
    $(".my_friend").click(function() {
      execute_change($(this)); /* alle Infos befinden sich im Element welches angeklickt wurde */
    })

    </script>

  </body>
  </html>
