<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class BackupFilesDirectoryPackage extends Package {

	protected $pkgHandle = 'backup_files_directory';
	public function getPackageName() { return t('Backup Files Directory'); }
	public function getPackageDescription() { return t("Retrieve a ZIP of your site's /files/ directory via the dashboard."); }
	protected $appVersionRequired = '5.6';
	protected $pkgVersion = '1.0';

	public function install() {
		$pkg = parent::install();
		$this->installOrUpgrade($pkg);
	}

	public function upgrade() {
		$this->installOrUpgrade($this);
		parent::upgrade();
	}

	public function installOrUpgrade($pkg) {
		$this->getOrAddSinglePage($pkg, '/dashboard/system/backup_restore/files', t('Backup Files Directory'));
	}
	
	
	private function getOrAddSinglePage(&$pkg, $cPath, $cName = '', $cDescription = '') {
		Loader::model('single_page');
		
		$sp = SinglePage::add($cPath, $pkg);
		
		if (is_null($sp)) {
			//SinglePage::add() returns null if page already exists
			$sp = Page::getByPath($cPath);
		} else {
			//Set page title and/or description...
			$data = array();
			if (!empty($cName)) {
				$data['cName'] = $cName;
			}
			if (!empty($cDescription)) {
				$data['cDescription'] = $cDescription;
			}
			
			if (!empty($data)) {
				$sp->update($data);
			}
		}
		
		return $sp;
	}
	

}