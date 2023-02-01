<?php
//include 'inc/db.php'; //TODO
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
						if (this.status == 200) {
							console.log('authentication successful');
							const resp = JSON.parse(this.responseText);
							document.getElementById('welcome_user').innerText = resp['authenticated_user'];
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
				xhttp.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
				let user = document.getElementById('user').value;
				let pass = document.getElementById('pass').value;
				xhttp.send(JSON.stringify({'user':user, 'pass': pass}));
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
	$json = json_decode(file_get_contents('php://input'));
	
	if (isset($json->user) && isset($json->pass)) {
		// MOCK, real db logic here:
		if ($json->user === 'asd' && $json->pass === 'asd') {
			$_SESSION['username'] = $json->user;
			header('HTTP/1.1 200 OK');
			header('Content-Type: application/json; charset=utf-8');
			echo json_encode(['authenticated_user' => $json->user]);
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
		header('HTTP/1.1 500 Internal Server Error');
		die();
	}
} else {
	// unsupported HTTP method
	header('HTTP/1.1 501 Not implemented');
	die();
}
header('HTTP/1.1 500 Internal Server Error');
?>
