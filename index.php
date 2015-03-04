<?php
$carpeta_archivo = "AvancePresupuesto";
//$carpeta_archivo = "DrivePHP";
$include_path = '/opt/lampp/htdocs/AvancePresupuesto/';
//$include_path = "";
require_once $include_path.'google-api-php-client/src/Google/autoload.php';

function makeArrayIDsWithFilesInFolder($service, $folderId) {
  $pageToken = NULL;
  $array_ids = array();
  do {
    try {
      $parameters = array();
      if ($pageToken) {
        $parameters['pageToken'] = $pageToken;
      }
      $children = $service->children->listChildren($folderId, $parameters);
      foreach ($children->getItems() as $child) {
        $array_ids[] = $child->getId();
      }
      $pageToken = $children->getNextPageToken();
    } catch (Exception $e) {
      print "An error occurred: " . $e->getMessage();
      $pageToken = NULL;
    }
  } while ($pageToken);
  
  return $array_ids;
}

function printFile($service, $fileId) {
  $array_archivos = array();
  try {
    $file = $service->files->get($fileId);
    //print_r($file);
    print "Id: " . $fileId . "<br/>";
    $array_download = $file->getExportLinks();
    print "Title: " . $array_download['text/csv'] . "<br/>";
    print "Title: " . $file->getTitle(). "<br/>";
    print "Description: " . $file->getDescription(). "<br/>";
    print "MIME type: " . $file->getMimeType(). "<br/>";
    print "Download: https://docs.google.com/spreadsheet/ccc?key=".$fileId."&output=csv<br/>";
    $url = "https://docs.google.com/spreadsheet/ccc?key=".$fileId."&output=csv";
    $cmd = "wget --no-check-certificate --content-disposition \"$url\" -O ".$fileId.".csv";
    print $cmd;
    /*
    $url = "https://docs.google.com/spreadsheets/d/".$fileId."/export?gid=0&format=csv";
    $source = file_get_contents($url);
    file_put_contents($fileId.'.csv', $source);
    file_put_contents($fileId.".csv", fopen($array_download['text/csv'], 'r'));
    */

  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
  return $array_archivos;
}


function getFile($service, $fileId) {
  $array_archivo = array();
  try {
    $file = $service->files->get($fileId);
    //print_r($file);
    $array_archivo['id'] = $fileId;
    $array_archivo['download_csv'] = $file->getExportLinks();
    $array_archivo['titulo'] = $file->getTitle();
    $array_archivo['mime_type'] = $file->getMimeType();
    $array_archivo['descripcion'] = $file->getDescription();
    
  } catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
  }
  
  //print_r($array_archivo);
  return $array_archivo;
}

function printParents($service, $fileId) {
  try {
    $parents = $service->parents->listParents($fileId);
    foreach ($parents->getItems() as $parent) {
      print 'File Id: ' . $parent->getId();
    }
  } catch (Exception $e) {
    print "An error occurred: " . $e->getMessage();
  }
}



session_start();

function principal($carpeta_archivo){
    $archivos=array();
    $client = new Google_Client();
    $client->setAuthConfigFile('client_secret_166812253810-gg40f3a5e94blpb3k1263knjb7jvcnrp.apps.googleusercontent.com.json');

    $client->addScope(Google_Service_Drive::DRIVE);
    $client->addScope(Google_Service_Drive::DRIVE_FILE);
    $client->addScope(Google_Service_Drive::DRIVE_READONLY);
    $client->addScope(Google_Service_Drive::DRIVE_METADATA_READONLY);
    $client->addScope(Google_Service_Drive::DRIVE_APPDATA);
    $client->addScope(Google_Service_Drive::DRIVE_APPS_READONLY);


    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
        $client->setAccessToken($_SESSION['access_token']);
        $drive_service = new Google_Service_Drive($client);
        $files_list = $drive_service->files->listFiles(array())->getItems();

        $presupuestos_folder='0B9IF70UV9P0nUGdqakktTFNIb3c';
        $ids_archivos = makeArrayIDsWithFilesInFolder($drive_service,$presupuestos_folder);

        foreach($ids_archivos as $archivo){

            $archivos[] = getFile($drive_service, $archivo);
            //print_r($archivos);

        }
    
    } 
    else {
        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/'.$carpeta_archivo.'/oauth2callback.php';
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }
    return $archivos;
}
?>
<!DOCTYPE html>
<html lang="es">
    


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap-theme.min.css">
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
    <title>Avance Presupuestal</title>
</head>
<body>
    <?php $presupuestos = principal($carpeta_archivo);?>
    <div class="container">
        <div style="text-align: center;">
            <h2>Generación de datos crudos para Avance Presupuestal y Flujo</h2>
        </div>
        <a href="#" class="btn btn-success">Ver Historial</a>
        <br/>
        <br/>
        <br/>
        <table width="100%">
            <tr>
                <td width="50%" style="vertical-align: top;">
                    <ul style="list-style-type: none;">
                        <li>Presupuestos 2015 (<a href="#">Cambiar Carpeta</a>)</li>
                        <li>&nbsp;</li>
                        
                        <?php 
                        foreach($presupuestos as $presupuesto){
                            if($presupuesto['mime_type']=='application/vnd.google-apps.spreadsheet')
                                echo "<li>".$presupuesto['titulo']."</li>";
                        }
                        ?>
                    </ul>
                </td>
                <td width="50%" style="vertical-align: top;">
                    <ul style="list-style-type: none;">
                        <li>Documentos de ejecución de presupuestos</li>
                        <li>&nbsp;</li>
                        <li>Ingresos &nbsp; - (Drive)</li>
                        <li>&nbsp;</li>
                        <li>Reembolsos &nbsp; -(Drive)</li>
                        <li>&nbsp;</li>
                        <li>Anticipos &nbsp; 
                            <?php 
                            //print_r($_SESSION);
                            if(isset($_SESSION["archivo_1"])):
                            
                        
                                echo "</br>(". $_SESSION["archivo_1"] .")";
                            else: ?>
                            <li><a href="https://hormiga.turbinehq.com/employees/7442/external_csv.csv?demand_kind=expenses&demand_state%5B%5D=filtering&demand_state%5B%5D=first&demand_state%5B%5D=pending&demand_state%5B%5D=rejected&demand_state%5B%5D=payment&demand_state%5B%5D=completed&demand_state%5B%5D=second&demand_state%5B%5D=filtering&token=cc54d675e01150329549399ef74042981c03c7d0">Descargar Aquí</a>
                            <form action="upload.php" method="post" enctype="multipart/form-data">
                                Subir archivo:
                                <input type="file" name="fileToUpload_1" id="fileToUpload_1">
                                <input type="submit" value="Subir Archivo" name="submit">
                            </form>
                            <?php endif; ?>
                           
                        </li>
                        <li>&nbsp; </li>
                        <li>Compras&nbsp;
                            
                        </li>
                        <li>
                            <?php 
                            if(isset($_SESSION["archivo_2"])):
                                echo "(". $_SESSION["archivo_2"] .")";
                            else: ?>
                            <a href="https://hormiga.turbinehq.com/employees/7442/external_csv.csv?demand_kind=purchases&token=a1159cc06707b68bbe42f89142b6e0fb3c792eb6">Descargar Aquí</a>
                            <form action="upload.php" method="post" enctype="multipart/form-data">
                                Subir archivo:
                                <input type="file" name="fileToUpload_2" id="fileToUpload_2">
                                <input type="submit" value="Subir Archivo" name="submit">
                            </form>
                            <?php endif; ?>
                        </li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;">
                    <br/>
                    <br/>
                    <a href="#" class="btn btn-success">Generar Avance Presupuestal y Flujo</a>     
                </td>
            </tr>
        </table>
        
    <?php
        //print_r();
    ?>
    </div>
</body>
</html>