<?php
  $var = 'none';
  $var2 = 'none';
 ?>
<?php
ini_set('memory_limit','512M');
ini_set('max_execution_time','300');
require 'vendor/autoload.php';

use \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use \PhpOffice\PhpSpreadsheet\Writer\Csv;
error_reporting(0);
require_once 'login.php';

$mysql_con = mysqli_connect($server,$username,$password,$database);

if(isset($_POST['submit'])){
      $currentDir = getcwd();
      $uploadDirectory = "/uploads/";

      $errors = []; // Store all foreseen and unforseen errors here

      $fileExtensions = ['csv','xlsx']; // Get all the file extensions

      $fileName = $_FILES['myfile']['name'];
      $fileSize = $_FILES['myfile']['size'];
      $fileTmpName  = $_FILES['myfile']['tmp_name'];
      $fileType = $_FILES['myfile']['type'];
      $fileExtension = strtolower(end(explode('.',$fileName)));

      $uploadPath = $currentDir . $uploadDirectory . basename($fileName);
	  //echo $new_path;
      //echo $uploadPath;
      $zipPath = $currentDir."/ZipFolder";
      $path = str_replace('\\', '/', $uploadPath);
      //echo $path;
      //echo $fileName;
      if(!empty($fileName) === true)
      {
            //********************************************************************************************************

                if (! in_array($fileExtension,$fileExtensions)) {
                    $errors[] = "This file extension is not allowed. Please upload a JPEG or PNG file";
                }

                if ($fileSize > 200000000) {
                    $errors[] = "This file is more than 2MB. Sorry, it has to be less than or equal to 2MB";
                }

                if (empty($errors)) {
                    $didUpload = move_uploaded_file($fileTmpName, $uploadPath);

                    if ($didUpload) {
                        //echo "The file " . basename($fileName) . " has been uploaded";
                    } else {
                        echo "An error occurred somewhere. Try again or contact the admin";
                    }
                } else {
                    foreach ($errors as $error) {
                        echo $error . "These are the errors" . "\n";
                    }
                }





            if($fileExtension=='xlsx'){
                  $xls_file = "$fileName";
                  //echo $xls_file;
                  $reader = new Xlsx();
                  $spreadsheet = $reader->load($xls_file);

                  $loadedSheetNames = $spreadsheet->getSheetNames();

                  $writer = new Csv($spreadsheet);
                  foreach($loadedSheetNames as $sheetIndex => $loadedSheetName) {
                      $writer->setSheetIndex($sheetIndex);
                      $writer->save("uploads/".$loadedSheetName.'.csv');
                      $handle = fopen("uploads/".$loadedSheetName.'.csv', "r");
                      $header = fgetcsv($handle,1000,",");
                      $new_path = "uploads/".$loadedSheetName.'.csv';
          		  	    $query = "DROP TABLE my_table";
                      $mysql_con->query($query);

                      $header_sql = array();
                      foreach($header as $h){
                          $header_sql[] = '`'.$h.'` VARCHAR(100)';
                      }
                      $sql[] = 'CREATE TABLE my_table  ('.implode(',',$header_sql).')';
                      $string_version = implode(',', $sql);

                      $mysql_con->query($string_version);

          		  	$activate = "SET GLOBAL local_infile = 'ON'";
          		  	$mysql_con->query($activate);
          		  	$query = "LOAD DATA LOCAL INFILE '$new_path' INTO TABLE my_table FIELDS TERMINATED BY ','  LINES TERMINATED BY '\n'  IGNORE 1 ROWS";
                     	//echo $query;
                      $result = $mysql_con->query($query);
          		  	$time = 'Time';
                      foreach ($header_sql as $check_time) {
                        if(preg_match("/{$time}/i",$check_time))
                          $time_col = $check_time;
                      }
                      //echo $header_sql[0];
                      //echo $time_col;
                      $time_col = explode("`",$time_col);
          		  	//echo $result;
                      $query = "UPDATE `my_table` SET `$time_col[1]` =TRIM(BOTH '\"' FROM `$time_col[1]`)";
                      $mysql_con->query($query);
                      $query = "UPDATE `my_table` SET `$time_col[1]` =STR_TO_DATE(`$time_col[1]`,  '%c/%e/%Y %k:%i')";
                      //echo $query;
                      $mysql_con->query($query);
                      mysqli_close($mysql_con);
                      $var2 = 'block';
                    }
            }
            elseif($fileExtension=='csv'){
              $handle = fopen("uploads/$fileName", "r");
              $header = fgetcsv($handle,1000,",");

  		  	$query = "DROP TABLE my_table";
              $mysql_con->query($query);

              $header_sql = array();
              foreach($header as $h){
                  $header_sql[] = '`'.$h.'` VARCHAR(100)';
              }
              $sql[] = 'CREATE TABLE my_table  ('.implode(',',$header_sql).')';
              $string_version = implode(',', $sql);

              $mysql_con->query($string_version);

  		  	$activate = "SET GLOBAL local_infile = 'ON'";
  		  	$mysql_con->query($activate);
  		  	$query = "LOAD DATA LOCAL INFILE '$path' INTO TABLE my_table FIELDS TERMINATED BY ','  LINES TERMINATED BY '\n'  IGNORE 1 ROWS";
             	//echo $query;
              $result = $mysql_con->query($query);
  		  	$time = 'Time';
              foreach ($header_sql as $check_time) {
                if(preg_match("/{$time}/i",$check_time))
                  $time_col = $check_time;
              }
              //echo $header_sql[0];
              //echo $time_col;
              $time_col = explode("`",$time_col);
  		  	//echo $result;
              //$query = "UPDATE `my_table` SET `$time_col[1]` =STR_TO_DATE(`$time_col[1]`,  '%d-%c-%Y %H:%i')";
              $query = "UPDATE `my_table` SET `$time_col[1]` =STR_TO_DATE(`$time_col[1]`,  '%c/%e/%Y %k:%i')";
              //echo $query;
              $mysql_con->query($query);
              mysqli_close($mysql_con);
              $var2 = 'block';
            }




          }
}

if(isset($_POST['submit2'])){

  //$table_name = $_POST['table'];
  $from_date = $_POST['from_date'];
  $to_date = $_POST['to_date'];

  //echo $from_date;
  //************************************************************************
  //$file_path = "ZipFolder/file.csv";
  //echo $file_path;
  //$fp = fopen($file_path, 'w');
  //$array_new = array("Call From","Time","Provider","Location");
  //fputcsv($fp,$array_new);
  //$query = "SELECT * FROM $table_name WHERE `Provider` LIKE '%IDEA%'";
  //************************************************************************
  $query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name= 'my_table'";
  $result = $mysql_con->query($query);
  while($row = $result->fetch_assoc()){
    $res[] = $row;
  }
  //echo $res[3][1];
  $col_array = array_column($res,'COLUMN_NAME');
  //echo $col_array[3];
  $time = 'Time';
  foreach ($col_array as $check_time) {
    if(preg_match("/{$time}/i",$check_time))
      $time_col = $check_time;
  }
  $query = "SELECT * FROM my_table WHERE `$time_col` >= '$from_date 00:00:00' AND `$time_col`<= '$to_date 23:59:59'";
  //SELECT * FROM my_table WHERE `Start Time` <= '2019-03-29 23:59:00' AND `Start Time` >= '2019-03-29 20:22:00'
  //echo $query;
  $result = $mysql_con->query($query);
  $count = 0;
  $file_number = 0;
  $array_new = $col_array;
  while($row = $result->fetch_assoc()) {
          if($count == 0){
            //echo newFile;
            $file_path = "ZipFolder/file{$file_number}.csv";
            $fp = fopen($file_path, 'w');
            fputcsv($fp,$array_new);
          }
          $count = $count + 1;
          fputcsv($fp, $row);

          if($count == 1048575){
            $count = 0;
            $file_number = $file_number + 1;
          }
  }
  fclose($fp);
}

if(isset($_POST['submit3'])){
  $currentDir = getcwd();
  $currentDir = str_replace('\\', '/', $currentDir);
  $zipPath = $currentDir."/ZipFolder";

  //$path2 = $_POST['path2'];
  //echo $path2;
  // Get real path for our folder
  $rootPath = realpath($zipPath);

  // Initialize archive object
  $zip = new ZipArchive();
  $zip->open('ZipFolder.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

  // Create recursive directory iterator
  /** @var SplFileInfo[] $files */
  $files = new RecursiveIteratorIterator(
      new RecursiveDirectoryIterator($rootPath),
      RecursiveIteratorIterator::LEAVES_ONLY
  );

  foreach ($files as $name => $file)
  {
      // Skip directories (they would be added automatically)
      if (!$file->isDir())
      {
          // Get real and relative path for current file
          $filePath = $file->getRealPath();
          $relativePath = substr($filePath, strlen($rootPath) + 1);

          // Add current file to archive
          $zip->addFile($filePath, $relativePath);
      }
  }

  // Zip archive will be created only after closing object
  $zip->close();
  foreach ($files as $name => $file){
      if(is_file($file))
            unlink($file);
  }
  $var = 'block';
}
if(isset($_POST['submit4'])){
  $file = "ZipFolder.zip";
  header('Content-type: application/x-download');
  header('Content-Disposition: attachment; filename="'.$file.'"');
  header('Content-Length: '.filesize($file));
  readfile($file);
}
?>

<html>
  <head>
    <style>
    .form-style-5{
    max-width: 500px;
    padding: 10px 20px;
    background: #f4f7f8;
    margin: 10px auto;
    padding: 20px;
    background: #f4f7f8;
    border-radius: 8px;
    font-family: Georgia, "Times New Roman", Times, serif;
    }

    .form-style-5 fieldset{
    border: none;
    }
    .form-style-5 legend {
    font-size: 1.4em;
    margin-bottom: 10px;
    }
    .form-style-5 label {
    display: block;
    margin-bottom: 8px;
    }

    .form-style-5 .number {
    background: #1abc9c;
    color: #fff;
    height: 30px;
    width: 30px;
    display: inline-block;
    font-size: 0.8em;
    margin-right: 4px;
    line-height: 30px;
    text-align: center;
    text-shadow: 0 1px 0 rgba(255,255,255,0.2);
    border-radius: 15px 15px 15px 0px;
    }

    .form-style-5 input[type="submit"],
    .form-style-5 input[type="button"]
    {
    position: relative;
    display: block;
    padding: 19px 39px 18px 39px;
    color: #FFF;
    margin: 0 auto;
    background: #1abc9c;
    font-size: 18px;
    text-align: center;
    font-style: normal;
    width: 100%;
    border: 1px solid #16a085;
    border-width: 1px 1px 3px;
    margin-bottom: 10px;
    }
    .form-style-5 input[type="submit"]:hover,
    .form-style-5 input[type="button"]:hover
    {
    background: #109177;
    }
    .form-style-5 input[type="text"],
    .form-style-5 input[type="file"],
    .form-style-5 input[type="date"],
    .form-style-5 input[type="datetime"],
    .form-style-5 input[type="email"],
    .form-style-5 input[type="number"],
    .form-style-5 input[type="search"],
    .form-style-5 input[type="time"],
    .form-style-5 input[type="url"],
    .form-style-5 textarea,
    .form-style-5 select {
    font-family: Georgia, "Times New Roman", Times, serif;
    background: rgba(255,255,255,.1);
    border: none;
    border-radius: 4px;
    font-size: 15px;
    margin: 0;
    outline: 0;
    padding: 10px;
    width: 100%;
    box-sizing: border-box;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    background-color: #e8eeef;
    color:#8a97a0;
    -webkit-box-shadow: 0 1px 0 rgba(0,0,0,0.03) inset;
    box-shadow: 0 1px 0 rgba(0,0,0,0.03) inset;
    margin-bottom: 30px;
    }
    .form-style-5 input[type="text"]:focus,
    .form-style-5 input[type="file"],
    .form-style-5 input[type="date"]:focus,
    .form-style-5 input[type="datetime"]:focus,
    .form-style-5 input[type="email"]:focus,
    .form-style-5 input[type="number"]:focus,
    .form-style-5 input[type="search"]:focus,
    .form-style-5 input[type="time"]:focus,
    .form-style-5 input[type="url"]:focus,
    .form-style-5 textarea:focus,
    .form-style-5 select:focus{
    background: #d2d9dd;
    }
</style>
  </head>
  <body>
    <div class="form-style-5">
      <div class="new">
    <legend><span class="number">1</span>Upload Data</legend>
    <form  action="" method="POST" enctype="multipart/form-data">
      <table name="First">
        <tr>
          <td colspan="2"><input style="width:100%;border-radius:4px;font-weight:bold" type="file" name="myfile" id="fileToUpload"></td>
        </tr>
        <!--
        <tr>
          <td>Path:</td>
          <td><input style="border-radius:4px" type="text" name="path"></td>
        </tr>-->
        <tr>
          <td colspan="2"><input style="width:100%;border-radius:4px;font-weight:bold" type="submit" name="submit" value="UploadFile & LoadData"></td>
        </tr>
      </table>
    </form>
    <p style="color:green; display:<?php echo $var2 ?>">Upload Success!!</p>
    <!--<p style="displa">Upload SuccessFull!!</p>-->
      <!--
      <input type="file" name="fileUpload"><br><br>
      Enter Table name:<input type="text" name="table">
      <input type="submit" name="submit1" value="Upload"><br><br>
    </form>-->

    <legend><span class="number">2</span>Select Date</legend>
    <form action="" method="POST">
      <table name="Second">
        <!--
        <tr>
          <td>DateFormat:</td>
          <td><p>2019-04-23</p></td>
        </tr>-->
        <tr>
          <td>Fromdate:</td>
          <td>Todate:</td>
        </tr>
        <tr>

          <td><input style="border-radius:4px" type="date" name="from_date"></td>
          <td><input style="border-radius:4px" type="date" name="to_date"></td>
        </tr>

        <tr>
          <td colspan="2"><input style="width:100%;border-radius:4px;font-weight:bold" type="submit" name="submit2" value="GenerateCSV"></td>
        </tr>
      </table>
    </form>
    <!--
      Enter Table name:<input type="text" name="table"><br>
      Fromdate:<input type="text" name="from_date"><br>
      Todate:<input type="text" name="to_date"><br><br>
      <input type="submit" name="submit2" value="RunQuery">
    </form>-->
    <legend><span class="number">3</span>Create ZIP & Download</legend>
    <table>
      <form action="" method="POST">
        <!--
        <tr>
          <td>ZipPath:</td>
          <td><input style="border-radius:4px" type="text" name="path2"></td>
        </tr>-->
        <tr>
          <td colspan="2">
            <input style="width:100%;border-radius:4px;font-weight:bold" type="submit" name="submit3" value="CreateZip">
          </td>
        </tr>
      </form>
        <tr>
          <td colspan="2">
            <form action="" method="POST">
              <input style="width:100%;display:<?php echo $var ?>;border-radius:4px;font-weight:bold" type="submit" name="submit4" value="Download">
            </form>
          </td>
        </tr>
    </table>
<!--
      <form action="" method="POST">
        <input type="submit" name="submit3" value="CreateZip">
      </form>

      <form action="" method="POST">
        <input type="submit" name="submit4" value="Download">
      </form>-->
    </div>
  </div>
  </body>
</html>
