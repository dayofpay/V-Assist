<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
  die("Грешка: " . $conn->connect_error);
}

if(isset($_POST['drLicenseNumber'])){
  $getLength = strlen($_POST['drLicenseNumber']);
}
if(isset($_POST['drLicenseNumber']) && $getLength >= 8 && $getLength <= 17){
    $unicodeString = urlencode($_POST['drLicenseNumber']);
    $url = 'https://check.bgtoll.bg/check/vignette/plate/BG/' . $unicodeString;
    $curl = curl_init();

    $headers = array(
        'Accept: application/json, text/plain, */*',
        'Accept-Encoding: gzip, deflate, br',
        'Accept-Language: bg-BG,bg;q=0.9',
    );
    curl_setopt($curl, CURLOPT_URL, 'https://check.bgtoll.bg/check/vignette/plate/BG/'. $unicodeString);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curl);
    
    curl_close($curl);
    $result = file_get_contents($url);

}
 if($getLength < 9 || $getLength > 17){
  echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
  <i class="uil uil-times-circle"></i> Регистрационният номер ' . $_POST['drLicenseNumber'] . ' изглежда, че не съществува !
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
exit(0);
}


  if($_POST['serviceType'] == "checkVignette"){

    if($getLength >= 9 && $getLength <= 17){
      $incrementValue = "UPDATE globalStats SET total_vignette_checks = total_vignette_checks + 1";
      $stmt = $conn->prepare($incrementValue);
      $stmt->execute();
      $stmt->close();
      
      $nonFormat_License = $_POST['drLicenseNumber'];
      $dateRequest = date("Y-m-d H:i:s");
      $ipRequest = $_SERVER['REMOTE_ADDR'];
      $addToDB = "INSERT INTO lNumbers (licenseNumber, dateRequest, ipRequest) VALUES (?,?,?)";
      $stmt = $conn->prepare($addToDB);
      $stmt->bind_param("sss", $nonFormat_License, $dateRequest, $ipRequest);
      $stmt->execute();
      $stmt->close();
    }
    
}
$jsonData = json_decode($result,true);


// VIGNETTE DATA
   $getStatus_Message = $jsonData['status']['message'];
   $canProceed = $jsonData['ok'];
   $country = $jsonData['vignette']['country'];
   $currency = $jsonData['vignette']['currency'];
   if($jsonData['vignette']['exempt'] === false){
    $exempt = "Не";
   }
   else if($jsonData['vignette']['exempt'] === true){
    $exempt = "Да";
   }
   $issueDate = $jsonData['vignette']['issueDateFormated'];
   $licenseNumberPlate = $jsonData['vignette']['licensePlateNumber'];
   $price = $jsonData['vignette']['price'] . $currency;
   $status = $jsonData['vignette']['status'];
   $validTill = $jsonData['vignette']['validityDateToFormated'];
   $vehicleClass = $jsonData['vignette']['vehicleClass'];
   $vignetteNumber = $jsonData['vignette']['vignetteNumber'];
   $stolenVehicle = "<STOLEN_DATA>";
   $validDate = new DateTime($validTill);
   $currentDate = new DateTime();
   
   $dateDiff = $validDate->diff($currentDate);
   $daysUntilExpire = $dateDiff->days;
   $dayType = "";
   if($daysUntilExpire ===1){
    $dayType = " ден";
   }
   else if($daysUntilExpire > 1){
    $dayType = " дни";
   }
// INSERT INTO ARCHIVE



$dateRequest = date("Y-m-d H:i:s");
$ipRequest = $_SERVER['REMOTE_ADDR'];
$addArchive = "INSERT INTO archive_vignette (country,currency,exempt,issueDate,licenseNumberPlate,price,status,validTill,vehicleClass,vignetteNumber,stolenVehicle,dateReqest,ipRequest) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($addArchive);
$stmt->bind_param("sssssssssssss", $country, $currency, $exempt, $issueDate, $licenseNumberPlate, $price, $status, $validTill, $vehicleClass, $vignetteNumber, $stolenVehicle, $dateRequest, $ipRequest);
if ($stmt->execute()) {
// Успешно
} else {
 // Неуспешно
}
$stmt->close();


// INSERT INTO ARCHIVE


// VIGNETTE DATA

if($jsonData !== false) {
    if($canProceed){
        echo '<div class="alert alert-success alert-icon alert-dismissible fade show" role="alert">
        <i class="uil uil-check-circle"></i> Информация за винетка  <br> Държава : ' . $country . '<br>
        Валута : '. $currency . '  <br> Конфискувана : ' . $exempt . ' <br> Дата на издаване : ' .
         $issueDate .
          '<br> Регистрационен номер : ' .
           $licenseNumberPlate .
            '<br> Цена : ' .
             $price .
              '<br> Статус : ' .
               $status . '<br>' .
                'Валидна до : ' .
                 $validTill .
                  '<br>Клас на МПС : '.  $vehicleClass .'
                  <br>Винетката ви изтича след : '.  $daysUntilExpire .'
                   '. $dayType . '<br>Номер на винетка : '.  $vignetteNumber .'
                  <br>Откраднато : '.  $stolenVehicle .'
 <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>';

      if($daysUntilExpire <= 30){
        echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
        <i class="uil uil-exclamation-triangle"></i> Вашата винетка изтича след ' . $daysUntilExpire . $dayType . '  !
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>';
      }
    }
    else if(!$canProceed){
        echo '<div class="alert alert-danger alert-icon alert-dismissible fade show" role="alert">
        <i class="uil uil-times-circle"></i> Няма намерена валидна винетка 
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>';
    }
    
} else {

  echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
  <i class="uil uil-times-circle"></i> Невалидни данни, опитайте пак
  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
}
?>