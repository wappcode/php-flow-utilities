<?php

declare(strict_types = 1);

namespace FlowUtilities;


/**
 * Recupera la extensión de un archivo
 * @return string
 */
function getFileExtension(string $fileName): ?string {
    $pos =strripos($fileName, ".");
    $extension=($pos===false) ? '':  substr($fileName, $pos+1);
    return $extension;
}

/**
 * Recupera solo el nombre del archivo ejemplo dirname1/dirname2/file.ext => file.ext
 * @return string
 */
function getFileNameOnly(string $src): string {
    $pos =strripos($src, DIRECTORY_SEPARATOR);
    $name=($pos===false) ? $src:  substr($src, $pos+1);
    return $name;
}
/**
 * Recupera solo el nombre del archivo ejemplo dirname1/dirname2/file.ext => file
 * @return string
 */
function getFileNameOnlyWithoutExtension(string $src): string {
    $pos =strripos($src, DIRECTORY_SEPARATOR);
    $name=($pos===false) ? $src:  substr($src, $pos+1);
    $extension = getFileExtension($name);
    $finalName = str_replace(".{$extension}", "", $name);
    return $finalName;
}

function standardizeFileName($fileName): string {
    
    $onlyName = getFileNameOnlyWithoutExtension($fileName);
    $extension = getFileExtension($fileName);
    $standardizeName = str_replace(
        array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ä", "ë", "ï", "ö", "ü", "Ä", "Ë", "Ï", "Ö", "Ü", "ñ", "Ñ"),
        array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N"),
        $onlyName
    );
    $separator = '-';
    $standardizeName = preg_replace('/\W+/', $separator, $standardizeName);
    $standardizeName = strtolower(trim($standardizeName, $separator));
    $resultName = $standardizeName.".".$extension;
    return strtolower($resultName);
}



/**
 * Recupera el mimeType de un archivo
 * @param $src la ruta completa del archivo
 * @return string
 */
function getFileType(string $src): string {
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($fileInfo, $src);
    finfo_close($fileInfo);
    return $type;
}

