<?php

require_once 'core.php';

if ( $_SERVER['REQUEST_METHOD'] === 'POST' )
	session::login();

$session = new session();

if ( array_key_exists( 'logout', $_GET ) )
	$session->logout();

if ( !$session->is_valid() ) {
	$page = new page( 'Σύνδεση' );
	$page->add_action( 'body_tag', function(): void {
?>
<form class="leaf flex-col w3-border root" method="post" autocomplete="off">
	<label class="leaf">
		<span>URL *</span>
		<input class="w3-input w3-border w3-round" name="url" required="required" autofocus="on">
	</label>
	<label class="leaf">
		<span>Authorization Header *</span>
		<input class="w3-input w3-border w3-round" name="auth" required="required">
	</label>
	<button type="submit" class="w3-button w3-border w3-round leaf">Σύνδεση</button>
</form>
<?php
	} );
	$page->echo_html();
	exit;
}

if ( array_key_exists( 'download', $_GET ) ) {
	$ch = curl_safe_init( $session->get_url() );
	curl_safe_setopt( $ch, CURLOPT_RETURNTRANSFER, TRUE );
	curl_safe_setopt( $ch, CURLOPT_HTTPHEADER, [ $session->get_auth(), ] );
	$result = curl_safe_exec( $ch );
	$httpcode = curl_safe_getinfo( $ch, CURLINFO_HTTP_CODE );
	curl_close( $ch );
	if ( $httpcode !== 200 )
		failure( 'error ' . $httpcode );
	if ( file_put_contents( $session->get_name(), $result ) === FALSE )
		failure( 'file_put_contents' );
	header( 'Location: .' );
	exit;
}

function excel2table( string $name ): array {
	$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load( $name );
	$worksheet = $spreadsheet->getSheetByName( 'RawReportData Data' );
	$cols = [];
	$j = 1;
	while ( TRUE ) {
		$cell = $worksheet->getCellByColumnAndRow( $j, 1 );
		$value = $cell->getValue();
		if ( is_null( $value ) )
			break;
		$cols[] = $value;
		$j++;
	}
	$rows = [];
	$i = 2;
	while ( TRUE ) {
		$cell = $worksheet->getCellByColumnAndRow( 1, $i );
		$value = $cell->getValue();
		if ( is_null( $value ) )
			break;
		$row = [];
		$j = 1;
		foreach ( $cols as $col ) {
			$cell = $worksheet->getCellByColumnAndRow( $j, $i );
			$value = $cell->getValue();
			if ( !is_null( $value ) ) {
				switch ( $cell->getDataType() ) {
					case 'inlineStr':
						$value = $value->getPlainText();
						break;
				}
			}
			$row[$col] = $value;
			$j++;
		}
		$rows[] = $row;
		$i++;
	}
	return [
		'cols' => $cols,
		'rows' => $rows,
	];
}

function player2team( string $player ) {
	return 'marousi';
}

$page = new page();

$page->add_action( 'nav_link', function() {
?>
<a class="leaf w3-button w3-border w3-round" href="?download">Λήψη</a>
<?php
} );

$page->add_action( 'body_tag', function(): void {
	global $session;
	if ( $session->has_file() ) {
		list( $cols, $rows ) = array_values( excel2table( $session->get_name() ) );
		$player_list = [];
		foreach ( $rows as $row ) {
			$name = $row['Player'];
			$score = $row['Current Total Score (points)'];
			$team = player2team( $name );
			$player_list[$name] = [
				'team' => $team,
				'score' => $score,
			];
		}
		var_dump( $player_list );
?>
<div class="leaf">
	<table>
		<thead>
			<tr>
<?php
		foreach ( $cols as $col ) {
?>
				<th><?= $col ?></th>
<?php
		}
?>
			</tr>
		</thead>
		<tbody>
<?php
		foreach ( $rows as $row ) {
?>
			<tr>
<?php
			foreach ( $row as $col => $value ) {
?>
				<td><?= $value ?></td>
<?php
			}
?>
			</tr>
<?php
		}
?>
		</tbody>
	</table>
</div>
<?php
	}
} );

$page->echo_html();
exit;
