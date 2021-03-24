<?php


class session {

	private $valid;
	private $url;
	private $auth;
	private $name;

	public function __construct() {
		$this->valid = TRUE;
		session_start();
		foreach ( [ 'url', 'auth', 'name' ] as $key ) {
			if ( array_key_exists( $key, $_SESSION ) && is_string( $_SESSION[$key] ) && !empty( $_SESSION[$key] ) )
				$this->$key = $_SESSION[$key];
			else
				$this->valid = FALSE;
		}
		session_abort();
	}

	public function is_valid(): bool {
		return $this->valid;
	}

	public static function login(): void {
		foreach ( [ 'url', 'auth' ] as $key ) {
			if ( !array_key_exists( $key, $_POST ) )
				exit( $key . ': not defined' );
			if ( !is_string( $_POST[$key] ) )
				exit( $key . ': not valid' );
		}
		session_start();
		$_SESSION['url'] = $_POST['url'];
		$_SESSION['auth'] = $_POST['auth'];
		$_SESSION['name'] = bin2hex( openssl_random_pseudo_bytes( 4 ) );
		session_commit();
		header( 'Location: .' );
		exit;
	}

	public function logout(): void {
		session_start();
		if ( $this->valid && $this->has_file() )
			unlink( $this->get_name() );
		session_destroy();
		header( 'Location: .' );
		exit;
	}

	public function get_url(): string {
		return $this->url;
	}

	public function get_auth(): string {
		return $this->auth;
	}

	public function get_name(): string {
		return 'tmp/' . $this->name . '.xlsx';
	}

	public function has_file(): bool {
		return file_exists( $this->get_name() );
	}
}
