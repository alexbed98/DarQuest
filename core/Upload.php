<?php

class Upload
{
    private static string $lastError = '';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    private static function setLastError(string $message): void
    {
        self::$lastError = $message;
    }

    public static function move(string $fileKey, string $destinationFolder, array $allowedTypes, int $maxByteSize = PHP_INT_MAX): string|false {

        self::setLastError('');

        if (!isset($_FILES[$fileKey])) {
            self::setLastError('Aucun fichier recu.');
            return false;
        }

        if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            self::setLastError('Erreur de televersement (code ' . (int) $_FILES[$fileKey]['error'] . ').');
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES[$fileKey]['tmp_name']);

        if ($_FILES[$fileKey]['size'] > $maxByteSize) {
            self::setLastError('Fichier trop volumineux (max ' . $maxByteSize . ' octets).');
            return false;
        }

        if (!in_array($mimeType, $allowedTypes, true)) {
            self::setLastError('Type MIME non autorise: ' . (string) $mimeType . '.');
            return false;
        }

        $tempName = $_FILES[$fileKey]['tmp_name'];
        $fileName = basename($_FILES[$fileKey]['name']);

        if (!is_dir($destinationFolder) && !mkdir($destinationFolder, 0775, true) && !is_dir($destinationFolder)) {
            self::setLastError('Impossible de creer le dossier de destination.');
            return false;
        }

        if (!is_writable($destinationFolder)) {
            self::setLastError('Dossier de destination non accessible en ecriture.');
            return false;
        }

        $destinationPath = $destinationFolder . '/' . $fileName;

        // éviter écrasement si meme nom de fichier
        $i = 1;
        while (file_exists($destinationPath)) {
            $fileName = pathinfo($_FILES[$fileKey]['name'], PATHINFO_FILENAME)
                . "_$i."
                . pathinfo($_FILES[$fileKey]['name'], PATHINFO_EXTENSION);

            $destinationPath = $destinationFolder . '/' . $fileName;
            $i++;
        }

        if (@move_uploaded_file($tempName, $destinationPath)) {
            return $fileName;
        }

        self::setLastError('Impossible de deplacer le fichier televerse.');

        return false;
    }
}
