<?php

require_once 'vendor/autoload.php';
require_once 'config.php';
require_once 'page.php';
require_once 'session.php';

function failure( string $message ): void {
	$page = new page( 'Σφάλμα' );
	$page->add_action( 'body_tag', function( string $message ): void {
?>
<div class="leaf flex-row root w3-border w3-border-orange w3-leftbar">
	<p class="leaf"><?= $message ?></p>
</div>
<?php
	}, $message );
	$page->echo_html();
}

function curl_safe_init( $url = NULL ) {
	$ch = curl_init( $url );
	if ( $ch === FALSE )
		failure( 'curl_init: error' );
	return $ch;
}

function curl_safe_setopt( $ch, int $option, $value ): void {
	if ( curl_setopt( $ch, $option, $value ) === FALSE )
		failure( sprintf( 'curl_setopt: %s', curl_error( $ch ) ) );
}

function curl_safe_exec( $ch ) {
	$return = curl_exec( $ch );
	if ( $return === FALSE )
		failure( sprintf( 'curl_exec: %s', curl_error( $ch ) ) );
	return $return;
}

function curl_safe_getinfo( $ch, $option ) {
	$return = curl_getinfo( $ch, $option );
	if ( $return === FALSE )
		failure( sprintf( 'curl_getinfo: %s', curl_error( $ch ) ) );
	return $return;
}
