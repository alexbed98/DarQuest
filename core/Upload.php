<?php

class Upload
{

    public static function move(string $fileKey, string $destinationFolder, array $allowedTypes, int $maxByteSize = PHP_INT_MAX): bool {

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES[$fileKey]['tmp_name']);

        if(
            $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK && 
            $_FILES[$fileKey]['size'] < $maxByteSize &&
            in_array($mimeType, $allowedTypes)
        ) {

            $tempName = $_FILES[$fileKey]['tmp_name'];
            $fileName = basename($_FILES[$fileKey]['name']);

            $destinationPath = $destinationFolder . '/' . $fileName;

            return move_uploaded_file($tempName, $destinationPath);

        }

        return false;

    }

}
