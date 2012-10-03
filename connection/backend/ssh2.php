<?php
/**
 * SSH, SCP and SFTP connections using PHPs ssh2-functions
 * 
 * @author Tuomas Angervuori <tuomas.angervuori@gmail.com>
 */
namespace Connection\Backend;

require_once(dirname(__FILE__) . '/../backend.php');
require_once(dirname(__FILE__) . '/../exception.php');

class Ssh2 implements \Connection\Backend {
	
	protected $ssh;
	protected $sftp;
	protected $dir;
	
	/**
	 * Establish a connection
	 */
	public function __construct($url, array $options = null) {
		
		$parsedUrl = parse_url($url);
		$fingerprint = null;
		$pubkey = null;
		
		//Check options
		if($options) {
			foreach($options as $option => $value) {
				if($option == 'fingerprint') {
					$fingerprint = $value;
				}
				else if($option == 'pubkey') {
					$pubkey = $value;
				}
				else {
					trigger_error("Unknown option '$option'",\E_USER_NOTICE);
				}
			}
		}
		
		//Initialize connection
		if(isset($parsedUrl['port']) && $parsedUrl['port']) {
			$this->ssh = ssh2_connect($parsedUrl['host'], $parsedUrl['port']);
		}
		else {
			$this->ssh = ssh2_connect($parsedUrl['host']);
		}
		if($this->ssh === false) {
			throw new \Connection\Exception("Could not connect to '{$parsedUrl['host']}'");
		}
		
		//Check server fingerprint (if defined)
		if($fingerprint) {
			$serverFingerprint = ssh2_fingerprint($this->ssh);
			if($fingerprint != $serverFingerprint) {
				throw new \Connection\Exception("Server fingerprint '$serverFingerprint' does not match!");
			}
		}
		
		//Provide authentication information
		if($pubkey) { //Using public key authentication
			if(isset($pubkey['passphrase']) && $pubkey['passphrase']) {
				$status = ssh2_auth_pubkey_file($this->ssh, $pubkey['user'], $pubkey['pubkeyfile'], $pubkey['privkeyfile'], $pubkey['passphrase']);
			}
			else {
				$status = ssh2_auth_pubkey_file($this->ssh, $pubkey['user'], $pubkey['pubkeyfile'], $pubkey['privkeyfile']);
			}
			if(!$status) {
				throw new \Connection\Exception("Could not login to '{$parsedUrl['host']}' as '{$parsedUrl['user']}' using public key authentication");
			}
		}
		else if(isset($parsedUrl['user']) && $parsedUrl['user']) { //Using login & password
			if(!ssh2_auth_password($this->ssh, $parsedUrl['user'], $parsedUrl['pass'])) {
				throw new \Connection\Exception("Could not login to '{$parsedUrl['host']}' as '{$parsedUrl['user']}'");
			}
		}
		
		//Set default directory
		if(isset($parsedUrl['path']) && $parsedUrl['path']) {
			$this->cd($parsedUrl['path']);
		}
	}
	
	/**
	 * Change directory
	 */
	public function cd($directory) {
		$this->dir = $directory;
	}
	
	/**
	 * Download a file 
	 * @note requires full path to file
	 */
	public function get($remoteFile) {
		
		$file = $this->_getFilename($remoteFile);
		$data = file_get_contents('ssh2.sftp://' . $this->_getSftp() . $file);
		
		if($data === false) {
			throw new \Connection\Exception("Could not download file '$file'");
		}
		
		return $data;
	}
	
	/**
	 * Upload a file 
	 */
	public function put($data, $remoteFile) {
		
		$file = $this->_getFilename($remoteFile);
		if(file_put_contents('ssh2.sftp://' . $this->_getSftp() . $file, $data) === false) {
			throw new \Connection\Exception("Could not upload file '$file'");
		}
	}
	
	/**
	 * List current directory
	 */
	public function ls() {
		
		$handle = opendir('ssh2.sftp://' . $this->_getSftp() . '/' . $this->dir);
		if(!$handle) {
			throw new \Connection\Exception("Listing directory '{$this->dir}' failed");
		}
		while(false !== ($file = readdir($handle))) {
			if($file != '.' && $file != '..') {
				$dir[] = $file;
			}
		}
		closedir($handle);
		sort($dir);
		
		return $dir;
	}
	
	/**
	 * Delete a file from remote server
	 */
	public function rm($remoteFile) {
		
		$file = $this->_getFilename($remoteFile);
		if(!ssh2_sftp_unlink($this->_getSftp(), $file)) {
			throw new \Connection\Exception("Could not remove file '$file'");
		}
	}
		
	/**
	 * Rename file in remote server
	 */
	public function mv($remoteFile, $newName) {
		
		$from = $this->_getFilename($remoteFile);
		$to = $this->_getFilename($newName);
		if(!ssh2_sftp_rename($this->_getSftp(), $from, $to)) {
			throw new \Connection\Exception("Could not rename file '$from' as '$to'");
		}
	}
	
	/**
	 * Create a directory in remote server
	 */
	public function mkdir($dirName) {
		
		$dir = $this->dir . '/' . $dirName;
		if(!ssh2_sftp_mkdir($this->_getSftp(), $dir)) {
			throw new \Connection\Exception("Could not create directory '$dir'");
		}
	}
	
	/**
	 * Remove a directory from remote server
	 */
	public function rmdir($dirName) {
		
		$dir = $this->dir . '/' . $dirName;
		if(!ssh2_sftp_rmdir($this->_getSftp(), $dir)) {
			throw new \Connection\Exception("Could not remove directory '$dir'");
		}
	}
	
	/**
	 * Return array of supported protocols
	 */
	public static function getAvailableProtocols() {
		$protocols = array();
		if(function_exists('ssh2_connect')) {
			$protocols = array('ssh','scp','sftp');
		}
		return $protocols;
	}
	
	/**
	 * Initialize SFTP subsystem
	 */
	protected function _getSftp() {
		if(!$this->sftp) {
			$this->sftp = ssh2_sftp($this->ssh);
			if($this->sftp === false) {
				throw new \Connection\Exception("Could not initialize SFTP subsystem");
			}
		}
		return $this->sftp;
	}
	
	/**
	 * Get absolute path
	 */
	protected function _getFilename($file) {
		if($this->dir) {
			return $this->dir . '/' . $file;
		}
		else {
			return '/' . $file;
		}
	}
}
