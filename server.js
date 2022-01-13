console.log("server.js");

var express = require("express");
var app = express();

var bodyParser = require("body-parser");
app.use(bodyParser.urlencoded());
app.use(bodyParser.json());

var http = require("http").createServer(app);
var io = require("socket.io")(http);

var mysql = require("mysql");
var connection = mysql.createConnection({
	"host": "localhost",
	"user": "root",
	"password": "",
	"database": "web_chat"
});

connection.connect(function (error) {
	app.get("/", function (request, result) {
		result.end("Hello world !");
	});

	app.use(function (req, res, next) {
	    res.setHeader('Access-Control-Allow-Origin', '*');
	    next();
	});

	app.get("/get_messages", function (request, result) {
		connection.query("SELECT * FROM messages", function (error, messages) {
			result.end(JSON.stringify(messages));
		});
	});

	io.on("connection", function (socket) {
		// console.log("socket connected = " + socket.id);

		socket.on("new_user", function (username) {
			connection.query("SELECT * FROM users WHERE username = '" + username + "'", function (error, result) {
				if (result.length == 0) {
					connection.query("INSERT INTO users(username) VALUES('" + username + "')", function (error, result) {
						io.emit("new_user", username);
					});
				} else {
					io.emit("new_user", username);
				}
			});
		});

		socket.on("delete_message", function (id) {
			connection.query("DELETE FROM messages WHERE id = '" + id + "'", function (error, result) {
				io.emit("delete_message", id);
			});
		})

		socket.on("new_message", function (data) {
			connection.query("INSERT INTO messages(username, message) VALUES('" + data.username + "', '" + data.message + "')", function (error, result) {
				data.id = result.insertId;
				io.emit("new_message", data);
			});
		});
	});
});

http.listen(3000, function () {
	console.log("Listening :3000");
});