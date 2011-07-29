<?php
/**
* DatabaseExport is a tool to export database via PHP and store it via FTP
*
* Copyright (c) 2011 Tomas Pavlatka (http://tomas.pavlatka.cz)
*
* @package DatabaseExport
*/

header("content-type: text/plain; charset=utf-8");

// Require classes.
require_once 'classes/Database.php';

// Export params.
$params = array(
    'database' => array(
	    'db_host'       => 'localhost',
	    'db_name'       => 'db_name',
	    'db_user'       => 'root',
	    'db_password'   => '',
	    'db_charset'    => 'utf8'),
    'ftp' => array(
        'ftp_server'    => '',
        'ftp_username'  => '',
        'ftp_password'  => '',
        'ftp_folder'    => ''
    ),
    'export' => array(
        'export_folder' => './backups/',
        'export_zip'    => 'bzip2',
    )
);

// Export database..
$dbObj = new PTX_Database($params['database']);
$dbObj->connect();

$dbTables = array();
if($dbObj->mysqlListTables()) {
	while($table = $dbObj->mysqlFetchArray()){
		$dbTables[] = (string)$table[0];	
	}
}
$exportedData = $dbObj->dbExport($dbTables);
	
// Save a file.
$fileName = $params['database']['db_name'].'-'.date('YmdHis').'.sql';
$filePath = $params['export']['export_folder'].$fileName;

if($params['export']['export_zip'] == 'bzip2') {
    $fileName .= '.bz2';
    $filePath .= '.bz2';
	$bz = bzopen($filePath, "w");
    bzwrite($bz, $exportedData);
    bzclose($bz);
} else {
	$fopen = fopen($filePath,'w+');
	fwrite($fopen,$exportedData);
	fclose($fopen);	
}

// Copy to FTP.
$ftpConnect = ftp_connect($params['ftp']['ftp_server']);
$ftpLogin = ftp_login($ftpConnect, $params['ftp']['ftp_username'], $params['ftp']['ftp_password']);
if($ftpLogin) {
    // Upload a file
    $destination = $params['ftp']['ftp_folder'].$fileName;
    $upload = ftp_put($ftpConnect, $destination, $filePath, FTP_BINARY); 	
}
ftp_close($ftpConnect); 




