<?php

class Comment extends Model {

	public static $table = 'comments';

	public static function paginate($page = 1, $perpage = 10) {
		$query = Query::table(static::$table);

		$count = $query->count();

		$results = $query->take($perpage)->skip(($page - 1) * $perpage)->sort('date', 'desc')->get();

		return new Paginator($results, $count, $page, $perpage, url('comments'));
	}

	public static function notify($comment) {
		$uri = Uri::build(array('path' => Uri::make('admin/comments/edit/' . $comment['id'])));

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		$headers .= 'From: notifications@' . $_SERVER['HTTP_HOST'] . "\r\n";

		$message = '<html>
			<head>
				<title>New comment has been added</title>
				<style>
					body {font: 15px/25px "Helvetica Neue", "Open Sans", "DejaVu Sans", "Arial", sans-serif;}
					table {margin: 1em 0; border-collapse: collapse;}
					table td {padding: 6px 8px; border: 1px solid #ccc;}
					h2, p {margin: 0 0 1em 0;}
				</style>
			</head>
			<body>
				<h2>A new comment has been submitted to your site.</h2>

				<table>
					<tr>
						<td>Name</td>
						<td>' . $comment['name'] . '</td>
					</tr>
					<tr>
						<td>Email</td>
						<td>' . $comment['email'] . '</td>
					</tr>
					<tr>
						<td>Comment</td>
						<td>' . $comment['text'] . '</td>
					</tr>
				</table>

				<p><a href="' . $uri . '">View</a></p>
			</body>
		</html>';

		// notify administrators
		foreach(User::where('role', '=', 'administrator')->get() as $user) {
			$to = $user->real_name . ' <' . $user->email . '>';
			mail($to, 'New comment', $message, $headers);
		}
	}

}