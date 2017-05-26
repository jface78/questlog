<!DOCTYPE html>
<html>
  <head>
    <meta charset=utf-8> 
    <title>QUESTLOG</title>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script type="text/javascript">
      window.jQuery || document.write('<script type="text/javascript" src="js/plugins/jquery-3.2.1.min.js"><\/script>');
    </script>
    <script type="text/javascript" src="js/plugins/jquery.qtip.min.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    
    <link rel="stylesheet" href="css/plugins/jquery.qtip.min.css" type="text/css">
    <link rel="stylesheet" href="css/plugins/font-awesome.min.css" type="text/css">
    <link rel="stylesheet" href="css/main.css" type="text/css">
  </head>
  <body>
    <div class="wrapper">
    <header>
      <div class="leftContent">
        <img id="logo" src="img/logo_day.gif" alt="QuestLog">
        <h4>This is QuestLog. RPGs and collaborative storytelling.</h4>
        <h3>Current Status: CLOSED BETA</h3>
      </div>
      <section class="loginBox">
        <form id="login" method="POST" accept-charset="utf-8">
          <ul>
            <li><input type="text" id="user" placeholder="user" required></li>
            <li><input type="password" id="pass" placeholder="passwd" required></li>
            <li><input type="submit" value="login"></li>
          </ul>
        </form>
      </section>
    </header>
    <main></main>
    </div>
    <footer>&copy;2017 Questlog, All Rights Reserved<br>blah blah blah legal mumbo jumbo no one reads</footer>
  </body>
</html>