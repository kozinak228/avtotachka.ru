<?php
$server = '31.31.196.77';
$ftp_user_name = 'u3414051';
$ftp_user_pass = '5IS0o5TmRS5bQEjG';
$file = 'dinamic-site.sql';
$remote_file = 'dinamic-site.sql';

$conn_id = ftp_connect($server);
if (!$conn_id) { die("ftp_connect failed"); }
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
if (!$login_result) { die("ftp_login failed"); }
ftp_pasv($conn_id, true);

if (ftp_put($conn_id, $remote_file, $file, FTP_BINARY)) {
    echo "Successfully uploaded $file\n";
} else {
    echo "There was a problem while uploading $file\n";
}
ftp_close($conn_id);
?>