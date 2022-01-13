<link rel="stylesheet" type="text/css" href="css/style.css">

<form method="POST" onsubmit="return setUsername();" id="form-login">
	<div class="form-group">
		<input type="text" autocomplete="off" id="username" placeholder="Enter username">
	</div>

	<div class="form-group">
		<input type="submit" value="Select">
	</div>
</form>

<div id="chat-box">
	<div id="all-chats">
		<ul id="messages" style="max-height: 1150px; overflow: scroll;"></ul>
	</div>

	<div id="send-message" style="display: none;">
		<form method="POST" onsubmit="return sendMessage(this);">
			<div class="form-group">
				<input type="text" autocomplete="off" name="message" id="message" placeholder="Enter message">
			</div>

			<div class="form-group">
				<input type="submit" value="Send">
			</div>
		</form>
	</div>
</div>

<script src="js/jquery.js"></script>
<script src="js/socket.io.js"></script>

<script>
	var url = "http://192.168.43.64:3000";
	var io = io(url);
	var username = "";

	function setUsername() {
		username = $("#username").val();
		$("#send-message").show();

		$.ajax({
			url: url + "/get_messages",
			method: "GET",
			success: function (response) {
				response = JSON.parse(response);
				var messages = document.getElementById("messages");

				for (var a = 0; a < response.length; a++) {
					var li = document.createElement("li");
					li.id = "message-" + response[a].id;

					if (response[a].username == "") {
						li.innerHTML = response[a].message;
					} else {
						li.innerHTML = "<b>" + response[a].username + ":</b> " + response[a].message;
					}
					
					if (response[a].username == username) {
						li.innerHTML += "<button class='btn-delete' data-id=" + response[a].id + " onclick='deleteMessage(this);'>Delete</button>";
					}

					messages.appendChild(li);
				}

				io.emit("new_user", username);
			}
		});

		return false;
	}

	function sendMessage(form) {
		var message = form.message.value;
		io.emit("new_message", {
			message: message,
			username: username
		});
		form.message.value = "";

		return false;
	}

	function deleteMessage(self) {
		var id = self.getAttribute("data-id");
		io.emit("delete_message", id);
	}

	io.on("delete_message", function (id) {
		document.getElementById("message-" + id).innerHTML = "<i>Message has been deleted</i>";
	});

	io.on("new_message", function (data) {
		var messages = document.getElementById("messages");
		var li = document.createElement("li");
		li.id = "message-" + data.id;

		if (data.username == "") {
			li.innerHTML = data.message;
		} else {
			li.innerHTML = "<b>" + data.username + ":</b> " + data.message;
		}

		if (data.username == username) {
			li.innerHTML += "<button class='btn-delete' data-id=" + data.id + " onclick='deleteMessage(this);'>Delete</button>";
		}

		messages.appendChild(li);
	});

	io.on("new_user", function (username) {
		var messages = document.getElementById("messages");
		var li = document.createElement("li");
		li.innerHTML = "<b>" + username + "</b> just joined the chat";
		messages.appendChild(li);
	});
</script>