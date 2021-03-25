<?php


class page {

	private $title;
	private $action_list;

	public function __construct( string $title = config::SITE_NAME ) {
		$this->title = $title;
		$this->action_list = [];
		$this->add_action( 'nav_link', function(): void {
?>
<a class="leaf w3-button w3-border w3-round" href="?logout" style="order: 1;">Αποσύνδεση</a>
<?php
		} );
	}

	public function add_action( string $key, callable $fn, ...$args ): void {
		if ( !array_key_exists( $key, $this->action_list ) )
			$this->action_list[$key] = [];
		$action = [
			'fn' => $fn,
			'args' => $args,
		];
		$this->action_list[$key][] = $action;
	}

	function do_action( string $key ): void {
		if ( !array_key_exists( $key, $this->action_list ) )
			return;
		foreach ( $this->action_list[$key] as $action )
			$action['fn']( ...$action['args'] );
	}

	public function echo_html(): void {
?>
<!DOCTYPE html>
<html lang="el">
	<head>
		<meta charset="UTF-8" />
		<meta name="author" content="constracti" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<title><?= config::SITE_NAME ?></title>
		<link rel="stylesheet" type="text/css" href="https://www.w3schools.com/w3css/4/w3.css" />
		<link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" />
		<style>
h1, h2, h3, h4, h5, h6, a, p, th, td, label, span, input, button {
	font-family: "Montserrat", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
a, button {
	font-weight: bold;
}
.flex-row {
	display: flex;
}
.flex-col {
	display: flex;
	flex-direction: column;
}
.flex-wrap {
	flex-wrap: wrap;
}
.flex-justify-between {
	justify-content: space-between;
}
.flex-justify-center {
	justify-content: center;
}
.flex-justify-end {
	justify-content: flex-end;
}
.flex-align-start {
	align-items: flex-start;
}
.flex-align-center {
	align-items: center;
}
.flex-align-end {
	align-items: flex-end;
}
.flex-grow {
	flex-grow: 1;
}
.flex-noshrink {
	flex-shrink: 0;
}
.root {
	padding: 8px;
}
.root .leaf {
	margin: 8px;
}
table {
	border-collapse: collapse;
	width: 100%;
	display: table;
	border: 1px solid #ccc;
}
table thead {
	color: #000;
	background-color: #9e9e9e;
}
table th,
table td {
	padding: 8px;
	text-align: left;
	vertical-align: top;
}
table tbody tr:nth-child(even) {
	background-color: #f1f1f1;
}
		</style>
	</head>
	<body class="flex-col root">
		<div class="flex-row flex-justify-between flex-align-center">
			<h1 class="leaf"><?= $this->title ?></h1>
			<div class="flex-row">
<?php
		$this->do_action( 'nav_link' );
?>
			</div>
		</div>
<?php
		$this->do_action( 'body_tag' );
?>
	</body>
</html>
<?php
		exit;
	}
}
