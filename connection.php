<?php
/**
 * Establish a connection to a server using different PHP backends
 * 
 * @author Tuomas Angervuori <tuomas.angervuori@gmail.com>
 * @license http://opensource.org/licenses/LGPL-3.0 LGPL v3
 */

require_once(dirname(__FILE__) . '/connection/backend/ssh2.php');
require_once(dirname(__FILE__) . '/connection/backend/ftp.php');
require_once(dirname(__FILE__) . '/connection/backend/curl.php');
require_once(dirname(__FILE__) . '/connection/exception.php');

class Connection {
	
	protected $backend;
	
	public function __construct($url, array $options = null) {
		
		$urlParts = parse_url($url);
		if(!isset($urlParts['scheme'])) {
			throw new \Connection\Exception('Scheme not defined');
		}
		
		$scheme = strtolower($urlParts['scheme']);
		
		//Primary option, try to use ssh2 functions as backend
		if(in_array($scheme, \Connection\Backend\Ssh2::getAvailableProtocols())) {
			$this->backend = new \Connection\Backend\Ssh2($url, $options);
		}
		//Secondary option, try to use ftp functions as backend
		else if(in_array($scheme, \Connection\Backend\Ftp::getAvailableProtocols())) {
			$this->backend = new \Connection\Backend\Ftp($url, $options);
		}
		//Third option, use curl functions as backend
		else if(in_array($scheme, \Connection\Backend\Curl::getAvailableProtocols())) {
			$this->backend = new \Connection\Backend\Curl($url, $options);
		}
		else {
			throw new \Connection\Exception("Unsupported protocol '{$urlParts['scheme']}'");
		}
	}
	
	/**
	 * Change directory
	 */
	public function cd($directory) {
        return $this->backend->cd($directory);
	}
	
	/**
	 * Change directory
	 */
	public function pwd() {
	   return $this->backend->pwd();
	}
		
	/**
	 * Download a file 
	 */
	public function get($remoteFile) {
		return $this->backend->get($remoteFile);
	}
	
	/**
	 * Upload a file 
	 */
	public function put($data, $remoteFile) {
		return $this->backend->put($data, $remoteFile);
	}
	
	/**
	 * List current directory
	 */
	public function ls() {
		return $this->backend->ls();
	}

	/**
	 * File or directory exists
	 */
	public function exists($path) {
		return $this->backend->exists($path);
	}
	
	
	/**
	 * Delete a file from remote server
	 */
	public function rm($remoteFile) {
		return $this->backend->rm($remoteFile);
	}
	
	/**
	 * Rename file in remote server
	 */
	public function mv($remoteFile, $newName) {
		return $this->backend->mv($remoteFile, $newName);
	}
	
	/**
	 * Create a directory in remote server
	 */
	public function mkdir($dirName) {
		return $this->backend->mkdir($dirName);
	}
	
	/**
	 * Remove a directory from remote server
	 */
	public function rmdir($dirName) {
		return $this->backend->rmdir($dirName);
	}
	
	/**
	 * Return array of supported protocols
	 */
	public static function getAvailableProtocols() {
		$protocols = array_merge(\Connection\Backend\Ssh2::getAvailableProtocols(), \Connection\Backend\Ftp::getAvailableProtocols(), \Connection\Backend\Curl::getAvailableProtocols());
		$protocols = array_unique($protocols);
		return $protocols;
	}
		
}
