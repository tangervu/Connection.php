<?php
/**
 * Interface for backend types
 * 
 * @author Tuomas Angervuori <tuomas.angervuori@gmail.com>
 * @license http://opensource.org/licenses/LGPL-3.0 LGPL v3
 */

namespace Connection;

interface Backend {
	
	/**
	 * Establish a connection
	 */
	public function __construct($url, array $options = null);
	
	/**
	 * Change directory
	 */
	public function cd($directory);
	
	/**
	 * Download a file 
	 */
	public function get($remoteFile);
	
	/**
	 * Upload a file 
	 */
	public function put($data, $remoteFile);
	
	/**
	 * List current directory
	 */
	public function ls();
	
	/**
	 * Delete a file from remote server
	 */
	public function rm($remoteFile);
	
	/**
	 * Rename file in remote server
	 */
	public function mv($remoteFile, $newName);
	
	/**
	 * Create a directory in remote server
	 */
	public function mkdir($dirName);
	
	/**
	 * Remove a directory from remote server
	 */
	public function rmdir($dirName);
	
	/**
	 * Return array of supported protocols
	 */
	public static function getAvailableProtocols();
}
