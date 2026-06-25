<?php

namespace App\Shared\Domain\Service;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

class SFTPFileUploader
{
    public function uploadFileBySftpWithPassword(
        string $host,
        string $user,
        string $password,
        string $remoteFile,
        string $localFile,
        int    $port = 22
    ): bool
    {
        $sftp = new SFTP($host, $port);

        if (!$sftp->login($user, $password)) {
            return false;
        }

        $sftp->put($remoteFile, $localFile, SFTP::SOURCE_LOCAL_FILE);
        $sftp->disconnect();

        return true;
    }

    public function uploadFileBySftpWithKeyFile(
        string $host,
        string $user,
        string $keyFile,
        string $remoteFile,
        string $localFile,
        int    $port = 22
    ): bool
    {
        $sftp = new SFTP($host, $port);
        $key = PublicKeyLoader::load(file_get_contents($keyFile));

        if (!$sftp->login($user, $key)) {
            return false;
        }

        $sftp->put($remoteFile, $localFile, SFTP::SOURCE_LOCAL_FILE);
        $sftp->disconnect();

        return true;
    }
}
