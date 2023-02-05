<?php
include 'inc/db.php'; 
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
<!DOCTYPE html>
<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
		<script>

			function do_login() {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4) {
						if (this.status == 243) {

							//create todo_list div
							let div = document.createElement("div");
							div.id = "todo_list";
							document.getElementById('todo_div').appendChild(div);

							let parser = new DOMParser();
							let xmlDoc = parser.parseFromString(this.response,"text/xml");
							document.getElementById('welcome_user').innerText = xmlDoc.getElementsByTagName("username")[0].childNodes[0].nodeValue;
							$count_todo = xmlDoc.getElementsByTagName("todos").length;

							for (let i = 0; i < $count_todo; i++) {

								let todo = xmlDoc.getElementsByTagName("todos")[i].childNodes[1].textContent;
								let todo_id = xmlDoc.getElementsByTagName("todos")[i].childNodes[0].textContent;

								let div = document.createElement("div");
								div.id = todo_id;

								let parag = document.createElement('p');
								parag.innerHTML = todo;
								parag.style = "display:inline"

								let button = document.createElement('button');
								button.innerHTML = 'X';
								button.onclick = deltodo;
								
								document.getElementById('todo_list').appendChild(div);
								document.getElementById(todo_id).appendChild(button);
								document.getElementById(todo_id).appendChild(parag);
							}
							document.getElementById('login_div').style.display = 'none';
							document.getElementById('main_div').style.display = 'block';
							document.getElementById('todo_div').style.display = 'block';

						} else {
							document.getElementById('login_message').innerText = 'Authentication failed, try again';
							console.log('authentication failed');
						}
					}
				};
				xhttp.open('POST', 'todo.php', true);
				
				let user = document.getElementById('user').value;
				let pass = document.getElementById('pass').value;
				let req = "<xml><user>"+user+"</user><pass>"+pass+"</pass></xml>";

				xhttp.setRequestHeader('Content-Type', 'text/xml');
				xhttp.send(req);
			}
			
			function sendtodo() {
				var xhttp = new XMLHttpRequest();
				var todo = document.getElementById('todo_input').value;
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4) {
						if (this.status == 222) {
							let parser = new DOMParser();
							let xmlDoc = parser.parseFromString(this.response,"text/xml");
							let todo_id = xmlDoc.getElementsByTagName("todo_id")[0].textContent;
							console.log(todo_id);

							let div = document.createElement("div");
							div.id = todo_id;

							let parag = document.createElement('p');
							parag.innerHTML = todo;
							parag.style = "display:inline"

							let button = document.createElement('button');
							button.innerHTML = 'X';
							button.onclick = deltodo;
							
							document.getElementById('todo_list').appendChild(div);
							document.getElementById(todo_id).appendChild(button);
							document.getElementById(todo_id).appendChild(parag);

						} else {
							// TODO ERROR HANDLING
						}
					}
				};
				xhttp.open('POST', 'todo.php', true);
				let req = "<xml><todo>"+todo+"</todo></xml>";

				xhttp.setRequestHeader('Content-Type', 'text/xml');
				xhttp.send(req);
			}

			function deltodo(click_env) {
				let todo_id = click_env.target.parentNode.id;
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 201) {
						$("#"+todo_id).remove();
					}
				};
				
				let req = "<xml><deltodo>"+todo_id+"</deltodo></xml>";

				xhttp.open("POST", "todo.php", true);
				xhttp.setRequestHeader('Content-Type', 'text/xml');
				xhttp.send(req);
			}

			function delall() {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 201) {
					$("#todo_list").remove();
					let div = document.createElement("div");
					div.id = "todo_list";
					document.getElementById('todo_div').appendChild(div);	
				}
				};
				let req = "<xml><delall>true</delall></xml>";
				xhttp.open("POST", "todo.php", true);
				xhttp.setRequestHeader('Content-Type', 'text/xml');
				xhttp.send(req);
			}

			function logout() {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
				if (this.readyState == 4 && this.status == 201) {
					$("#todo_list").remove();
					document.getElementById('login_div').style.display = 'block';
					document.getElementById('main_div').style.display = 'none';
					document.getElementById('todo_div').style.display = 'none';
				}
				};
				let req = "<xml><logout>true</logout></xml>";
				xhttp.open("POST", "todo.php", true);
				xhttp.setRequestHeader('Content-Type', 'text/xml');
				xhttp.send(req);
			}


		</script>
	</head>
	<body>
		<div id='login_div'>
			<p id=login_message>You are not logged in, please authenticate:</p>
			<label>Username&nbsp;</label>
			<input id='user' type='text' placeholder='Enter Username'>
			<label>Password&nbsp;</label>
			<input id='pass' type='password' placeholder='Enter Password'>
			<button id='login_submit' type='submit' onclick='do_login()'>Login</button>		
		</div>
		<div id='main_div' style='display:none'>
			<p>Welcome,&nbsp;<label id=welcome_user>nobody</label>!</p>
			<input id='todo_input' type='text' placeholder='To-DO eintragen'>
			<button id='3' type='submit' onclick='sendtodo()'>-></button> <br/>
			<button id='5' type='submit' onclick='delall()'>alle TO-DOs loeschen</button>
			<button id='6' type='submit' onclick='logout()'>Logout</button>
		</div>
		<div id='todo_div' style='display:none'>
		</div>
	</body>
</html>

<?php
// API, POST Handling

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// TODO: check content-type

	$req = file_get_contents('php://input');
	$xml_req=simplexml_load_string($req) or die("Error: Cannot create object");
	
	if (isset($xml_req->user) && isset($xml_req->pass)) {

		$sql_query = "select id as user_id from user_table where name=? and passwort=?";
		$stmt = $con->prepare($sql_query);
		$stmt->bind_param("ss", $xml_req->user, $xml_req->pass);
		$stmt->execute();
		$result = $stmt->get_result();
		$row = $result->fetch_assoc();

		if(!empty($row)){
			
			$_SESSION['username'] = (string)$xml_req->user;
			$_SESSION['user_id'] = $row['user_id'];

			$sql_query = "select todo, id from todo_table where UserId=?";
			$stmt = $con->prepare($sql_query);
			$stmt->bind_param("i", $row['user_id']);
			$stmt->execute();
			$result = $stmt->get_result();

			$xml_resp = new SimpleXMLElement('<xml/>');
			$xml_resp->addChild('username', $xml_req->user);
			$xml_resp->addChild('user_id', $row['user_id']);
			while ($row = $result->fetch_assoc()) {
				$todos = $xml_resp->addChild('todos');
				$todos->addChild('todo_id', $row['id']);
				$todos->addChild('todo', $row['todo']);
			}

			header('HTTP/1.1 243 OK');
			Header('Content-type: text/xml');
			print($xml_resp->asXML());
			die();
		} else {
			header('HTTP/1.1 401 unauthorized');
			die();
		}
	} elseif (isset($_SESSION['username'])) { 		// MEMBERS ONLY from here on
		if (isset($xml_req->todo)) {
			
			$user_id = strval($_SESSION['user_id']);
			$sql_insert = "INSERT INTO todo_table(UserId, todo) VALUES (?,?)";
			$sql_get_pkey = "SELECT LAST_INSERT_ID()";
			$stmt= $con->prepare($sql_insert);
			$stmt->bind_param("is", $user_id, $xml_req->todo);
			$stmt->execute();

			$todo_id = $con->query($sql_get_pkey);
			$todo_id= $todo_id->fetch_assoc();
			$todo_id = $todo_id['LAST_INSERT_ID()'];
		
			$xml_resp = new SimpleXMLElement('<xml/>');
			$xml_resp->addChild('todo_id', $todo_id);

			header('HTTP/1.1 222 OK');
			Header('Content-type: text/xml');
			print($xml_resp->asXML());
			die();
		}
		if (isset($xml_req->deltodo)){
			$todo_id = (string)$xml_req->deltodo;
			$sql_query = "DELETE FROM todo_table WHERE id=?";
			$stmt= $con->prepare($sql_query);
			$stmt->bind_param("i", $todo_id);
			$stmt->execute();
			header("HTTP/1.1 201 OK");
		}
		if(isset($xml_req->logout)){
			session_destroy();
			header("HTTP/1.1 201 OK");
			echo "logout";
		}
		if(isset($xml_req->delall)){
			$user_id = strval($_SESSION['user_id']);
			$sql_query = "DELETE FROM todo_table WHERE UserId=?";
			$stmt= $con->prepare($sql_query);
			$stmt->bind_param("i", $user_id);
			$stmt->execute();
			header("HTTP/1.1 201 OK");
			echo "delall";
		}
	} else {	// No session
		header('HTTP/1.1 505 Internal Server Error');
		die();
	}
} else { // unsupported HTTP method
	header('HTTP/1.1 501 Not implemented');
	die();
}
?>
