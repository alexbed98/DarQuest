<?php

class Upload
{
    public static function move(string $fileKey, string $destinationFolder, array $allowedTypes, int $maxByteSize = PHP_INT_MAX): string|false {

        if (!isset($_FILES[$fileKey])) {
            return false;
        }

        if ($_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES[$fileKey]['tmp_name']);

        if (
            $_FILES[$fileKey]['size'] > $maxByteSize ||
            !in_array($mimeType, $allowedTypes)
        ) {
            return false;
        }

        $tempName = $_FILES[$fileKey]['tmp_name'];
        $fileName = basename($_FILES[$fileKey]['name']);

        if (!is_dir($destinationFolder) && !mkdir($destinationFolder, 0775, true) && !is_dir($destinationFolder)) {
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

        if (move_uploaded_file($tempName, $destinationPath)) {
            return $fileName;
        }

        return false;
    }
}
