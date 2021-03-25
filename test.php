<?php

require_once 'core.php';

$page = new page( 'Έλεγχος' );

$nickname = NULL;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	if ( !array_key_exists( 'nickname', $_POST ) )
		failure( 'nickname: not defined' );
	if ( !is_string( $_POST['nickname'] ) )
		failure( 'nickname: not valid' );
	$nickname = $_POST['nickname'];
}

$page->add_action( 'body_tag', function(): void {
	global $nickname;
	if ( !is_null( $nickname ) ) {
		$team = config::player2team( $nickname );
?>
<div class="leaf flex-col root w3-border w3-border-blue w3-leftbar">
	<p class="leaf"><span>είσοδος: </span><?php var_dump( $nickname ) ?></p>
	<p class="leaf"><span>ομάδα: </span><?php var_dump( $team ) ?></p>
</div>
<?php
	}
?>
<form class="leaf flex-col w3-border root" method="post" autocomplete="off">
	<label class="leaf">
		<span>nickname</span>
		<input class="w3-input w3-border w3-round" name="nickname" required="required" autofocus="on">
	</label>
	<button type="submit" class="w3-button w3-border w3-round leaf">Έλεγχος</button>
</form>
<?php
} );

$page->echo_html();
