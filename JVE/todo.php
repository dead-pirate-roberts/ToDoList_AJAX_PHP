<?php
include 'inc/db.php'; 
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
?>
<!DOCTYPE html>
<html>
	<head>
		<script>
			function do_login() {

				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4) {
						if (this.status == 243) {
							console.log('authentication successful');

							let parser = new DOMParser();
							let xmlDoc = parser.parseFromString(this.response,"text/xml");

							document.getElementById('welcome_user').innerText = xmlDoc.getElementsByTagName("username")[0].childNodes[0].nodeValue;

							$count_todo = xmlDoc.getElementsByTagName("todos").length;
							console.log($count_todo);

							for (let i = 0; i < $count_todo; i++) {
								let todo = xmlDoc.getElementsByTagName("todos")[i].childNodes[1].textContent;
								let todo_id = xmlDoc.getElementsByTagName("todos")[i].childNodes[0].textContent;
								console.log(todo);


								let div = document.createElement("div");
								div.id = todo_id;

								let parag = document.createElement('p');
								parag.innerHTML = todo;
								parag.style = "display:inline"

								let button = document.createElement('button');
								button.innerHTML = 'X';
								button.onclick = deltodo(todo_id);


					
								
								document.getElementById('todo_div').appendChild(div);
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

				//xhttp.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
				//xhttp.send(JSON.stringify({'user':user, 'pass': pass}));
			}
			
			
			function sendtodo() {
				var xhttp = new XMLHttpRequest();
				xhttp.onreadystatechange = function() {
					if (this.readyState == 4) {
						if (this.status == 200) {
							console.log('request successful');
							const resp = JSON.parse(this.responseText);
							console.log(resp['todo']);
							let list = document.getElementById('todo_list');
							let li = document.createElement("li");
							li.appendChild(document.createTextNode(resp['todo']));
							list.appendChild(li);
						} else {
							// TODO ERROR HANDLING
						}
					}
				};

				xhttp.open('POST', 'todo.php', true);
				xhttp.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
				let todo = document.getElementById('todo_input').value
				xhttp.send(JSON.stringify({'todo':todo}));
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
			<button id='3' type='submit' onclick='sendtodo()'>-></button>
			<button id='5' type='submit' onclick='deltodo()'>alle TO-DOs loeschen</button>
			<button id='6' type='submit' onclick='logout()'>Logout</button>
		</div>
		<div id='todo_div' style='display:none'>
		<ol id='todo_list'>
		</ol>
		</div>
	</body>
</html>


<?php
// API, POST Handling

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// TODO: check content-type

	//$json = json_decode(file_get_contents('php://input'));
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

			$_SESSION['username'] = $xml_req->user;
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
		}







		if ($json->user === 'asd' && $json->pass === 'asd') {
			$_SESSION['username'] = $json->user;
			header('HTTP/1.1 243 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['authenticated_user' => $xml->user]);
			die();
		} else {
			header('HTTP/1.1 401 unauthorized');
			die();
		}
	} elseif (isset($_SESSION['username'])) { 		// MEMBERS ONLY from here on
		if (isset($json->todo)) {
			// TODO Save to DB, read from DB
			// kannst z.B. alle TODOs auslesen und in ein Array packen und zurückgeben
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['todo' => $json->todo]);
			die();
		}
	} else {
		header('HTTP/1.1 505 Internal Server Error');
		die();
	}
} else {
	// unsupported HTTP method
	header('HTTP/1.1 501 Not implemented');
	die();
}
header('HTTP/1.1 504 Internal Server Error');
?>
