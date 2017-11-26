<!DOCTYPE>
<html lang="en-US">
  <head>
    <meta charset="utf-8">
    <meta name="author" content="Donald Luc">
    <meta name="description" content="Notre Dame OIT Calendar Creator Web Application">

    <title>ND OIT Application</title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300|Sonsie+One" rel="stylesheet" type="text/css">
    <!-- link css here -->

    <!-- the below three lines fix HTML5 semantic elements in old versions of Internet Explorer -->
    <!--[if lt IE 9]>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.3/html5shiv.js"></script>
    <![endif]-->

  </head>

  <body>
  
    <header>
      <h1>ND OIT Google Calendar Creator</h1>
    </header>

    <nav>
      <ul>
        <li><a href="#">Notre Dame OIT</a></li>
        <li><a href="#">Sakai</a></li>
        <li><a href="#">Contacts</a></li>
      </ul>
    </nav>

    <main>
      <form method="post" action="index.php">
        <input name="netid"/>
        <input type="submit" name="start" value="Start"/>
      </form>

      <?php
      session_start();
      if (isset($_POST["start"]) && $_POST["netid"] != "") {
        $_SESSION["netid"] = $_POST["netid"];
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/ND_OIT_Calendar_Web_App/oauth2callback.php';
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
      }
      ?>
    </main>
    <footer>
      <p>Questions? Please <a href="mailto:dluc@nd.edu">email</a> us.</p>
    </footer>
  </body>
</html>
