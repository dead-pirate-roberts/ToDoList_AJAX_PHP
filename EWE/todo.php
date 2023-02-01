<?php
  session_start();
  include 'inc/db.php';


  if (isset($_SESSION['username']) and $_SERVER['REQUEST_METHOD'] === 'GET') { // Fallback if page is reloaded
    echo "Herzlich Willkommen ".$_SESSION['user_id'];
  } 
  elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {  //default
    echo '
    <!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  <script>
  function sendData() {
    $username = document.getElementById(3).value;
    $password = document.getElementById(5).value;
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 201) {  //login correct
        $("#login").remove();
        document.getElementById("main").innerHTML += this.response;
        }

      if (this.readyState == 4 && this.status == 200) {
        $("#wrongpw").remove();
        document.getElementById("main").innerHTML += "<p id=wrongpw>"+this.response+"</p>";

        }
    };
    xhttp.open("POST", "todo.php", true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("user="+$username+"&pwd="+$password);
  }
  function sendtodo() {
             $todo = document.getElementById("todo_field").value;
             var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                document.getElementById("main").innerHTML += this.response;
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
        <button id="6" type="submit" onclick="sendData()">Login</button>
    </div>
</div>
</body>
</html>
';
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
          <div id="todo">
          <input id="todo_field" type="text" placeholder="To-DO eintragen">
          <button type="submit" onclick="sendtodo()">-></button>
          <br>
          <button type="submit" onclick="deltodo()">alle TO-DOs loeschen</button>
          <button type="submit" onclick="logout()">Logout</button>
          </div>';

          while ($row = $result->fetch_assoc()) {
            echo $row['todo'];
            echo '<button type="submit" onclick="del_todo('.$row['id'].')">Logout</button>';
            echo '<br>';
          }
      }
      else{
          header("HTTP/1.1 200 OK");
          echo "Invalid username and password";
      }
  }
}

if(isset($_POST['todo']) and isset($_SESSION['username'])){
  $todo = $_POST['todo'];
  $user_id = $_SESSION['user_id'];
  $sql_query = "INSERT INTO todo_table(UserId, todo) VALUES (?,?)";
  $stmt= $con->prepare($sql_query);
  $stmt->bind_param("is", $user_id, $todo);
  $stmt->execute();
  echo 'i reveived '.$todo;

}
?>

