<?php
  session_start();
  include 'inc/db.php';


  if (isset($_SESSION['username']) and $_SERVER['REQUEST_METHOD'] === 'GET') { // Fallback if page is reloaded
    echo "Herzlich Willkommen ".$_SESSION['username'];
    echo '<script>
          function sendtodo() {
             $todo = document.getElementById(2).value;
             var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                document.getElementById("1").innerHTML += this.response;
                }
          
            };
             xhttp.open("POST", "todo.php", true);
             xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
             xhttp.send("todo="+$todo);
          }
      </script>
      <div id="1">
      <input id="2" type="text" placeholder="To-DO eintragen">
      <button id="3" type="submit" onclick="sendtodo()">-></button>
      </div>
      
      <div id="4">
      <button id="5" type="submit" onclick="deltodo()">alle TO-DOs loeschen</button>
      <button id="6" type="submit" onclick="logout()">Logout</button>
      </div>
    ';
  } 
  elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo '
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script>

function sendData() {
   $username = document.getElementById(3).value;
   $password = document.getElementById(5).value;
   var xhttp = new XMLHttpRequest();
   xhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 201) {
      $("#2").remove();
      $("#3").remove();
      $("#4").remove();
      $("#5").remove();
      $("#6").remove();
      $("#wrongpw").remove();
      $("#senddata").remove();
      document.getElementById("1").innerHTML += this.response;
      }

    if (this.readyState == 4 && this.status == 200) {
      $("#wrongpw").remove();
      document.getElementById("1").innerHTML += "<p id=wrongpw>"+this.response+"</p>";

      }
  };
   xhttp.open("POST", "todo.php", true);
   xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
   xhttp.send("user="+$username+"&pwd="+$password);
}
</script>

<div id="1">
<label id="2">Username</label>
<input id="3" type="text" placeholder="Enter Username">

<label id="4">Password</label>
<input id="5" type="password" placeholder="Enter Password">
<button id="6" type="submit" onclick="sendData()">Login</button>
</div>
';
}

if(isset($_POST['user'])){

  $uname = mysqli_real_escape_string($con,$_POST['user']);
  $password = mysqli_real_escape_string($con,$_POST['pwd']);

  if ($uname != "" && $password != ""){

      $sql_query = "select count(*) as countUser from user_table where name='".$uname."' and passwort='".$password."'";
      $result = mysqli_query($con,$sql_query);
      $row = mysqli_fetch_array($result);

      $count = $row['countUser'];

      if($count > 0){
          $_SESSION['username'] = $uname;
          header("HTTP/1.1 201 OK");
          echo "Herzlich Willkommen ".$_SESSION['username'];
          echo '<script>
          function sendtodo() {
             $todo = document.getElementById(2).value;
             var xhttp = new XMLHttpRequest();
             xhttp.onreadystatechange = function() {
              if (this.readyState == 4 && this.status == 200) {
                document.getElementById("1").innerHTML += this.response;
                }
          
            };
             xhttp.open("POST", "todo.php", true);
             xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
             xhttp.send("todo="+$todo);
          }
      </script>
      <div id="1">
      <input id="2" type="text" placeholder="To-DO eintragen">
      <button id="3" type="submit" onclick="sendtodo()">-></button>
      </div>
      
      <div id="4">
      <button id="5" type="submit" onclick="deltodo()">alle TO-DOs loeschen</button>
      <button id="6" type="submit" onclick="sendData()">Logout</button>
      </div>
    ';
      }
      else{
          header("HTTP/1.1 200 OK");
          echo "Invalid username and password";
      }
  }
}

if(isset($_POST['todo'])){
  echo 'i reveived todo';

}
?>


