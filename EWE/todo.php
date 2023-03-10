<?php
session_start();
include 'inc/db.php';


if (isset($_SESSION['username']) and $_SERVER['REQUEST_METHOD'] === 'GET') { // Fallback if page is reloaded
  echo "Herzlich Willkommen ".$_SESSION['user_id'];
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {  
?>
    <!DOCTYPE html>
    <html>
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <title></title>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
      <script>

      
      function logout() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 201) {
            $("#todo_head").remove();
            $("#todos").remove();
            document.getElementById('login').style.display = 'block';
          }
        };
        xhttp.open("POST", "todo.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("logout");
      }

      
      function login() {
        $username = document.getElementById(3).value;
        $password = document.getElementById(5).value;
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 201) {  //login correct
            document.getElementById('login').style.display = 'none';
            document.getElementById("main").innerHTML += this.response;
            }

          if (this.readyState == 4 && this.status == 200) {
            $("#wrongpw").remove();
            document.getElementById("login").innerHTML += "<p id=wrongpw>"+this.response+"</p>";

            }
        };
        xhttp.open("POST", "todo.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("user="+$username+"&pwd="+$password);
      }

      function deltodo(todo_id) {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 201) {
            $("#"+todo_id).remove();
          }
        };
        xhttp.open("POST", "todo.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("deltodo="+todo_id);
      }

      function delall() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
          if (this.readyState == 4 && this.status == 201) {
            $("#todos").remove();
            document.getElementById("todo_head").innerHTML += '<div id="todos">'
          }
        };
        xhttp.open("POST", "todo.php", true);
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhttp.send("delall");
      }


      function sendtodo() {
          $todo = document.getElementById("todo_field").value;
          var xhttp = new XMLHttpRequest();
          xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
              //$xml = $.parseXML(this.response);
              //$todo_id = $xml.getElementsByTagName("todo_id");
              $todo_id = this.response;
              document.getElementById("todos").innerHTML += '<div id='+$todo_id+'> <button type="submit" onclick="deltodo('+$todo_id+')">X</button> <p style="display: inline;">'+$todo+'</p> </div>';
              }
        
          };
          xhttp.open("POST", "todo.php", true);
          xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
          xhttp.send("todo="+$todo);
        }
      </script>
    </head>
    <body>
    <div id="main">
        <div id="login">
            <label id="2">Username</label>
            <input id="3" type="text" placeholder="Enter Username">

            <label id="4">Password</label>
            <input id="5" type="password" placeholder="Enter Password">
            <button id="6" type="submit" onclick="login()">Login</button>
        </div>
        

    </div>
    </body>
    </html>
<?php
}

if(isset($_POST['user'])){

  $uname = mysqli_real_escape_string($con,$_POST['user']);
  $password = mysqli_real_escape_string($con,$_POST['pwd']);

  if ($uname != "" && $password != ""){

      $sql_query = "select id as user_id from user_table where name=? and passwort=?";
      $stmt = $con->prepare($sql_query);
      $stmt->bind_param("ss", $uname, $password);
      $stmt->execute();
      $result = $stmt->get_result();
      $row = $result->fetch_assoc();
      

      if(!empty($row)){
          $_SESSION['username'] = $uname;
          $_SESSION['user_id'] = $row['user_id'];

          $sql_query = "select todo, id from todo_table where UserId=?";
          $stmt = $con->prepare($sql_query);
          $stmt->bind_param("i", $row['user_id']);
          $stmt->execute();
          $result = $stmt->get_result();
          header("HTTP/1.1 201 OK");
          echo '
          <div id="todo_head">
          <input id="todo_field" type="text" placeholder="To-DO eintragen">
          <button type="submit" onclick="sendtodo()">-></button>
          <br>
          <button type="submit" onclick="delall()">alle TO-DOs loeschen</button>
          <button type="submit" onclick="logout()">Logout</button>
          </div>
          <div id="todos">'
          ;

          while ($row = $result->fetch_assoc()) {
            echo '<div id='.$row['id'].'>';
            echo '<button type="submit" onclick="deltodo('.$row['id'].')">X</button>';
            echo '<p style="display: inline;">'.$row['todo'].'</p>';
            echo '</div>';
          }
          echo '</div>';
      }
      else{
          header("HTTP/1.1 200 OK");
          echo "Invalid username and password";
      }
  }
}

if(isset($_POST['todo']) and isset($_SESSION['username'])){
  $todo = $_POST['todo'];
  $user_id = strval($_SESSION['user_id']);
  $sql_insert = "INSERT INTO todo_table(UserId, todo) VALUES (?,?)";
  $sql_get_pkey = "SELECT LAST_INSERT_ID()";
  $stmt= $con->prepare($sql_insert);
  $stmt->bind_param("is", $user_id, $todo);
  $stmt->execute();
  $todo_id = $con->query($sql_get_pkey);
  $todo_id= $todo_id->fetch_assoc();
  $todo_id = $todo_id['LAST_INSERT_ID()'];
 // header('Content-Type: application/xml');
  //$output = "<root><user_id>".$user_id."</user_id><todo_id>".$todo_id."</todo_id></root>";
  //print ($output);
  echo $todo_id;  // return this as some actually usefull datatype JSON XML ? 
}

if(isset($_POST['deltodo']) and isset($_SESSION['username'])){
  $todo_id = $_POST['deltodo'];
  $sql_query = "DELETE FROM todo_table WHERE id=?";
  $stmt= $con->prepare($sql_query);
  $stmt->bind_param("i", $todo_id);
  $stmt->execute();
  header("HTTP/1.1 201 OK");
}

if(isset($_POST['logout']) and isset($_SESSION['username'])){
  session_destroy();
  header("HTTP/1.1 201 OK");
  echo "logout";
}


if(isset($_POST['delall']) and isset($_SESSION['username'])){
  $user_id = strval($_SESSION['user_id']);
  $sql_query = "DELETE FROM todo_table WHERE UserId=?";
  $stmt= $con->prepare($sql_query);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  header("HTTP/1.1 201 OK");
  echo "delall";
}
?>


