<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class DashboardSystemBackupRestoreFilesController extends Controller {
	
	private $fh;
	private $tmp;
	
	public function __construct() {
		$this->fh = Loader::helper('file');
		$this->tmp = $this->fh->getTemporaryDirectory() . '/files_directory_backups';
	}
	
	public function view() {
		$has_permission = $this->hasPermission();
		$this->set('has_permission', $has_permission);
		if ($has_permission) {
			$filenames = $this->fh->getDirectoryContents($this->tmp);
			$fileinfo = array();
			foreach ($filenames as $filename) {
				$path = $this->tmp . '/' . $filename;
				preg_match('/^files(.*)\.zip$/', $filename, $match);
				$created = date('Y-m-d @ H:i:s', $match[1]);
				$size = $this->getHumanFilesize(filesize($path));
				$fileinfo[] = array(
					'path' => $path,
					'name' => $filename,
					'created' => $created,
					'size' => $size,
				);
			}
			$this->set('files', $fileinfo);
		}
	}
	
	public function create_backup() {
		if ($this->hasPermission() && $this->post()) {
			if ($this->post('db_too')) {
				$this->createDatabaseBackup();
			}
			$this->zipFilesDirectory();
		}
		
		$this->redirectToView();
	}
	
	public function download_backup() {
		if ($this->hasPermission() && $this->post()) {
			$path = $this->getPostedFilePath();
			if (!empty($path)) {
				$this->fh->forceDownload($path);
				//forceDownload() calls "exit", so we're done
			}
		}
		
		$this->redirectToView();
	}
	
	public function delete_backup() {
		if ($this->hasPermission() && $this->post()) {
			$path = $this->getPostedFilePath();
			if (!empty($path)) {
				unlink($path);
			}
		}
		
		$this->redirectToView();
	}
	
	private function hasPermission() {
		// $u = new User;
		// return $u->isSuperUser();		
		$tp = new TaskPermission();
		return $tp->canBackup();
	}
	
	private function getPostedFilePath() {
		$ret_path = '';
		
		$filename = empty($_POST['file']) ? '' : $_POST['file'];
		if (!empty($filename)) {
			if (strpos($filename, '/') === false && strpos($filename, '.') !== 0) { //watch out for potential problems
				$path = $this->tmp . '/' . $filename;
				if (is_file($path)) {
					$ret_path = $path;
				}
			}
		}
		
		return $ret_path;
	}
	
	function getHumanFilesize($bytes, $decimals = 2) { //code taken from a comment on the php.net/filesize page
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
	
	private function redirectToView() {
		$this->redirect('/dashboard/system/backup_restore/files');
	}
	
	private function zipFilesDirectory() // code taken from http://stackoverflow.com/a/1334949/477513
	{	
		$source = DIR_FILES_UPLOADED_STANDARD;
		$destination = $this->tmp . '/files' . time() . '.zip';
		
		//create our temp subdirectory if it doesn't exist
		if (is_dir($this->tmp) !== true) {
			mkdir($this->tmp);
			file_put_contents($this->tmp . "/.htaccess","Order Deny,Allow\nDeny from all");
		}
		
	    if (!extension_loaded('zip') || !file_exists($source)) {
	        return false;
	    }

	    $zip = new ZipArchive();
	    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
	        return false;
	    }

	    $source = str_replace('\\', '/', realpath($source));
		
		//create top-level "files" directory inside the ZIP
		$zip->addEmptyDir('files/');
		
	    if (is_dir($source) === true)
	    {
	        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

	        foreach ($files as $file)
	        {
	            $file = str_replace('\\', '/', $file);

	            // Ignore "." and ".." folders
	            if( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) )
	                continue;
				
				$file = realpath($file);
				$real_files_dir = realpath(DIR_FILES_UPLOADED_STANDARD);
				
				//IGNORE THE CONTENTS OF THE /cache, /tmp, and /trash DIRECTORIES
				// (it's especially important that we ignore the ZIP file itself,
				// which lives in the tmp directory as it's being constructed)
				$cache_dir = $real_files_dir . '/cache';
				$tmp_dir = $real_files_dir . '/tmp';
				$trash_dir = $real_files_dir . '/trash';
				if (strpos($file, "{$cache_dir}/") === 0) {
					if ($file != ("{$cache_dir}/index.html")) {
						continue;
					}
				}
				if (strpos($file, "{$tmp_dir}/") === 0) {
					if ($file != ("{$tmp_dir}/index.html")) {
						continue;
					}
				}
				if (strpos($file, "{$trash_dir}/") === 0) {
					continue;
				}
				//END IGNORE cache/tmp/trash
				
				//WATCH OUT FOR THE DB BACKUP FILE -- NEED TO TEMPORARILY MODIFY ITS PERMISSIONS
				if (strpos($file, $real_files_dir . '/backups/dbu') === 0) {
					$db_file = realpath($file);
					if (is_file($db_file) === true) {
						chmod($db_file, 0666);
						$this->addFileToZip($zip, $db_file, $source);
						chmod($db_file, 000);
						continue;
					}
				}
				//END DB BACKUP FILE PERMISSIONS
				
	            if (is_dir($file) === true)
	            {
	                $zip->addEmptyDir('files/' . str_replace($source . '/', '', $file . '/'));
	            }
	            else if (is_file($file) === true)
	            {
					$this->addFileToZip($zip, $file, $source);
	            }
	        }
	    }
		
	    return $zip->close();
	}
	
	private function addFileToZip(&$zip, $file, $exclude_parent_path) {
		if (is_readable($file)) {
			$exclude_parent_path = rtrim($exclude_parent_path, '/') . '/';
			$destination = 'files/' . str_replace($exclude_parent_path, '', $file);
			$zip->addFromString($destination, file_get_contents($file));
		}
	}
	
	private function createDatabaseBackup() {
		Loader::library('backup');
		Backup::execute();
	}
	
}