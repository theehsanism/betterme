<?php
/**
 * Upload a file via FTP to a remote download host in a user's dedicated folder.
 *
 * @param string $localFile  مسیر فایل لوکال (محلی) که قرار است آپلود شود.
 * @param int    $userId     شناسه کاربر وردپرس.
 * @param string $filename   نام فایل برای ذخیره در سرور.
 * @return string|bool Returns the remote file path on success, or false on failure.
 */
function ftp_upload_file($localFile, $userId, $filename) {
    // اطلاعات هاست دانلود:
    $ftp_host = "185.204.197.5"; // آی‌پی یا آدرس FTP
    $ftp_username = "pz21301";
    $ftp_password = "jsFP4CT2";
    
    // مسیر ریموت پایه (می‌توانید در صورت نیاز مسیر را تغییر دهید)
    $remoteBaseDir = "/";

    // پوشه اختصاصی کاربر در هاست دانلود
    $userFolder = $remoteBaseDir . $userId;
    $remoteFile = $userFolder . "/" . $filename;

    // اتصال به FTP
    $connId = ftp_connect($ftp_host);
    if (!$connId) {
        error_log("FTP connection to $ftp_host failed.");
        return false;
    }

    // ورود به سیستم FTP
    if (!ftp_login($connId, $ftp_username, $ftp_password)) {
        error_log("FTP login failed for user $ftp_username.");
        ftp_close($connId);
        return false;
    }

    // تغییر به حالت نه‌فعال (Passive mode) به دلیل مسائل شبکه‌ای
    ftp_pasv($connId, true);

    // بررسی وجود پوشه کاربر، در صورت عدم وجود ایجاد می‌کنیم
    $directories = ftp_nlist($connId, $remoteBaseDir);
    $folderExists = false;
    if ($directories !== false) {
        foreach ($directories as $dir) {
            // ftp_nlist ممکن است مسیرها را به صورت مطلق یا نسبی برگرداند
            if (basename($dir) == $userId) {
                $folderExists = true;
                break;
            }
        }
    }

    if (!$folderExists) {
        if (!@ftp_mkdir($connId, $userFolder)) {
            error_log("Failed to create directory $userFolder on FTP server.");
            ftp_close($connId);
            return false;
        }
    }

    // آپلود فایل به پوشه اختصاصی
    if (!ftp_put($connId, $remoteFile, $localFile, FTP_BINARY)) {
        error_log("Failed to upload $localFile to $remoteFile on FTP server.");
        ftp_close($connId);
        return false;
    }

    // بستن اتصال FTP
    ftp_close($connId);

    return $remoteFile;
}
?>