<?php

namespace FlowUtilities;

use Exception;
use Flow\Basic;
use Flow\Config;
use Flow\Request;



class UploadFileManager {


    const FILENAME_SEPARATOR = '----.';


    /**
     * Carga un archivo por partes si aún no esta completo retorna null, si se completó retorna UploadedFileModel
     *
     * @param string $uploadsDir
     * @param string $tmpDir
     * @return UploadedFileModel|null
     */
    public static function uploadFile(string $uploadsDir, string $tmpDir): ?UploadedFileModel {

        if (!file_exists($uploadsDir)) {
            throw new Exception("The directory doesn't exist");
        }
        if (!file_exists($tmpDir)) {
            throw new Exception("The temp directory doesn't exist");
        }
        $config = new Config();
        $config->setTempDir($tmpDir);
        $request = new Request();
        $uploadFolder = $tmpDir;
        $requestName = $request->getFileName();
        $standardizeName = standardizeFileName($requestName);
        $uploadFileName = uniqid().self::FILENAME_SEPARATOR.$standardizeName; // The name the file will have on the server
        $uploadPath = $uploadFolder.DIRECTORY_SEPARATOR.$uploadFileName;
        if (Basic::save($uploadPath, $config, $request)) {
            return new UploadedFileModel($requestName,$uploadPath,$standardizeName);
        } else {
            return null;
        }
    }
    /**
     * Mueve y/o renombra un archivo desde el directorio temporal a la ubicación especificad
     * @param $src fullpath origin
     * @param $dest fullpath destiny
     * @param $overwrite bool cuando el valor es verdadero y existe un archivo con el mismo nombre que el destino lo sobreescribe
     * @return bool Retorna true si fue movido o renombrado false en caso contrario 
     */
    public static function mvFile(string $src, string $dest, bool $overwrite = false): bool {
        if(!file_exists($src) || is_dir($src)) {
            throw new Exception("The file doesn't exist");
        }
        if(file_exists($dest)){
            if($overwrite) {
                @unlink($dest);
            } else {
                throw new Exception("The file is duplicated", 400);
            }
        }
        $ok = @rename($src, $dest);
        return $ok;
        
        
    }

    /**
     * Elimina un archivo. El directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_dir
     * @param $path string relativePath del archivo origen
     * @return bool Retorna true si fue eliminado false en caso contrario 
     */
    public static function rmFile(string $path): bool {
        if(!file_exists($path) || is_dir($path)) {
            throw new Exception("The file doesn't exist");
        }
        $ok = @unlink($path);
        return $ok;
    }
    /**
     * Lee el contenido de un archivo  y aplica las cabeceras correspondientes para poderlo mostrar en un navegador
     * El directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_dir
     * Cache 2592000 =  cache de 30 dias 
     * 
     * @param $src string relativePath del archivo origen
     * @return void
     */
    public static function readFile(string $path, string $fileName = '', int $cacheTime = 2592000) {
        if(!file_exists($path) || is_dir($path)){
            throw new Exception("El archivo no existe", 404);
        }
        $contentType = getFileType($path);
        if(empty($fileName)) {
            $fileName = getFileNameOnly($path);
        } else {
            $extension = getFileExtension($path);
            $fileName = $fileName.".".$extension;

        }
        $size = filesize($path);
        header("Content-disposition: filename=$fileName");
        header("Content-type: $contentType");
        header('CacheTime-Control: max-age='.$cacheTime); // cache de 30 dias
        header('Expires: '. gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheTime));
        header('Content-Length: ' . $size);
        readfile($path);
        exit;
    }
     /**
     * Lee el contenido de un archivo  y aplica las cabeceras correspondientes para poderlo descargar en un navegador
     * El directorio base esta establecido en la configuracion de CoreConfigService con la clave core_upload_dir
     * Cache 86400 =  cache de 1 dia 
     * @param $src string relativePath del archivo origen
     * @return void
     */
    public static function downloadFile(string $path, string $fileName= '', int $cacheTime = 86400) {
        if (!file_exists($path) || is_dir($path)) {
            throw new Exception("El archivo no existe", 404);
        }
        $contentType ='application/octet-stream';
        if(empty($fileName)) {
            $fileName = getFileNameOnly($path);
        } else {
            $extension = getFileExtension($path);
            $fileName = $fileName.".".$extension;

        }
        $size = filesize($path);
        header("Content-disposition: attachment; filename=$fileName");
        header("Content-type: $contentType");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: max-age='.$cacheTime);
        header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $cacheTime));
        header('Content-Length: ' . $size);
        readfile($path);
        exit;
    }
    public static function uploadB64File(string $uploadDir, string $content, string $src) {
        $destPath = $uploadDir.DIRECTORY_SEPARATOR.$src;
        if(file_exists($destPath)){
            throw new Exception("File exist");
        }
          // open the output file for writing
          $ifp = fopen( $destPath, 'wb' ); 
    
          // split the string on commas
          // $data[ 0 ] == "data:image/png;base64"
          // $data[ 1 ] == <actual base64 string>
          $data = explode( ',', $content );
      
          // we could add validation here with ensuring count( $data ) > 1
          fwrite( $ifp, base64_decode( $data[ 1 ] ) );
      
          // clean up the file resource
          fclose( $ifp ); 
      
          return $destPath; 
    }
}