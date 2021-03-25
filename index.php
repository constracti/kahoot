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

function print_table( array $cols, array $rows ): void {
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

// TODO replace play with config::player2team

function player2team( string $name ) {
	switch ( $name ) {
		case 'test1':
			return 'ΚΘΓ';
		case 'test2':
			return 'ΠΚΓ';
		default:
			return NULL;
	}
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
		$quiz = NULL;
		list( $cols, $rows ) = array_values( excel2table( $session->get_name() ) );
		$player_list = [];
		foreach ( $rows as $row ) {
			$quiz = $row['Question Number'];
			$name = $row['Player'];
			$score = $row['Current Total Score (points)'];
			$team = player2team( $name );
			$player_list[$name] = [
				'team' => $team,
				'score' => $score,
			];
		}
		$score_list = [];
		foreach ( $player_list as $player ) {
			$team = $player['team'];
			if ( is_null( $team ) )
				continue;
			$league = config::get_league( $team );
			if ( !array_key_exists( $league, $score_list ) ) {
				$score_list[$league] = [];
			}
			if ( !array_key_exists( $team, $score_list[$league] ) ) {
				$score_list[$league][$team] = 0;
			}
			if ( $score_list[$league][$team] < $player['score'] )
				$score_list[$league][$team] = $player['score'];
		}
		$max_list = [];
		foreach ( array_keys( $score_list ) as $league ) {
			asort( $score_list[$league], SORT_NUMERIC );
			$score_list[$league] = array_reverse( $score_list[$league], TRUE );
			$max_list[$league] = max( ...array_values( $score_list[$league] ) );
		}
		// var_dump( $quiz, $score_list );
?>
<div class="flex-row">
<?php
		foreach ( array_keys( $score_list ) as $league ) {
?>
	<div class="leaf flex-col flex-grow root w3-border">
		<h2 class="leaf"><?= config::get_category( $league ) ?></h2>
<?php
			foreach ( array_keys( $score_list[$league] ) as $i => $team ) {
?>
		<div class="leaf flex-col w3-border">
			<div class="flex-row root">
				<div class="flex-row flex-grow">
					<span class="leaf w3-badge"><?= $i + 1 ?></span>
					<span class="leaf flex-grow"><?= config::get_location( $team ) ?></span>
				</div>
				<span class="leaf w3-tag w3-round"><?= $score_list[$league][$team] ?></span>
			</div>
			<div class="w3-border-top">
				<div class="w3-gray" style="height: 1em; width: <?= sprintf( '%.2f', 100 * $score_list[$league][$team] / $max_list[$league] ) ?>%"></div>
			</div>
		</div>
<?php
			}
?>
	</div>
<?php
		}
?>
</div>
<?php
	} else {
?>
<div class="leaf flex-row root w3-border w3-border-blue w3-leftbar">
	<p class="leaf">Δεν έχει μεταφορτωθεί το αρχείο των αποτελεσμάτων.</p>
</div>
<?php
	}
} );

$page->echo_html();
