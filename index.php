<!DOCTYPE>
<html>
<head>
  <title>ND OIT Application</title>
</head>
<body>
<p>
  <div>
    <h1>ND OIT Google Calendar Creator</h1>
  </div>

  <form method="post" action="index.php">
    <input name="netid"/>
    <input type="submit" name="start" value="Start"/>
  </form>
</p>

<p>
  <?php
  // -------------------------------------------------------------
  //                             REQUIRES
  // -------------------------------------------------------------



  // -------------------------------------------------------------
  //                            MAIN SCRIPT
  // -------------------------------------------------------------

  session_start();
  if (isset($_POST["start"]) && $_POST["netid"] != "") {
    $_SESSION["netid"] = $_POST["netid"];
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/ND_OIT_Calendar_Web_App/oauth2callback.php';
    header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
  }
  ?>
</p>
</body>
</html>
