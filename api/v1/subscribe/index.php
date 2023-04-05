<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";


$conn = new mysqli($servername, $username, $password, $dbname);
header('Content-Type: application/json; charset=utf-8');
if ($conn->connect_error) {
  die("Грешка: " . $conn->connect_error);
}
else {
    header('Content-Type', 'application/json; charset=UTF-8');
    if (empty($_POST) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $noPostData = array("Неуспешно" => "Моля, въведете POST дата");
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($noPostData, JSON_UNESCAPED_UNICODE);
        exit;
    }
      
    // CHECK VALID REQUEST SESSION
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $invalidRequest = array("Неуспешно" => "Не можете да пращате GET заявки към този адрес");
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($invalidRequest, JSON_UNESCAPED_UNICODE);
        exit;
    }
    

    if(isset($_POST['subscribe'])){
        if($_POST['subscribe'] == true){
            if(!empty($_POST['egn'])){
                if(!empty($_POST['driving_license_number'])){
                    if(!empty($_POST['lNumber'])){
                        if(!empty($_POST['email'])){
                            $dateRequest = date("Y-m-d H:i:s");
                            $ipRequest = $_SERVER['REMOTE_ADDR'];
                            $addEntry = "INSERT INTO subscriptions (email,egn,driving_license_num,subDate,ip,regNumber) VALUES (?, ?, ?, ?, ?,?)";
                            $stmt = mysqli_prepare($conn, $addEntry);
                            if (!$stmt) {
                                die("Грешка: " . mysqli_error($conn));
                            }
                            mysqli_stmt_bind_param($stmt, "ssssss", $_POST['email'], $_POST['egn'], $_POST['driving_license_number'], $dateRequest, $ipRequest,$_POST['lNumber']);
                            if (mysqli_stmt_execute($stmt)) {
                                $success = array("Успешно" => "Успешно се абонирахте, вече ще получавате месечно информация за глоби и винетка");
                                header('Content-Type: application/json; charset=utf-8');
                                echo json_encode($success, JSON_UNESCAPED_UNICODE);
                            } else {
                                http_response_code(400);

                                $unsuccess = array("Неуспешно" => "Неуспешно: " . mysqli_error($conn));
                                header('Content-Type: application/json; charset=utf-8');
                                echo json_encode($unsuccess, JSON_UNESCAPED_UNICODE);
                            }
                            mysqli_stmt_close($stmt);
                        }
                        else{
                            http_response_code(400);

                            $invalidEmail = array("Неуспешно" => "Моля, въведете валиден имейл");
                            header('Content-Type: application/json; charset=utf-8');
                            echo json_encode($invalidEmail, JSON_UNESCAPED_UNICODE);  
                        }
                    }
                    else{
                        http_response_code(400);

                        $invalidRegnum = array("Неуспешно" => "Моля, въведете регистрационен номер");
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode($invalidRegnum, JSON_UNESCAPED_UNICODE);
                    }
                }
                else{
                    http_response_code(400);

                    $invalidDrivingLicense = array("Неуспешно" => "Моля, въведете шофьорска книжка");
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode($invalidDrivingLicense, JSON_UNESCAPED_UNICODE);
                }
            }
            else{
                http_response_code(400);

                $invalidEgn = array("Неуспешно" => "Моля, въведете ЕГН");
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($invalidEgn, JSON_UNESCAPED_UNICODE);
            }
        }
    }
    else{
        http_response_code(400);

        $invalidMethod= array("Неуспешно" => "Невалиден метод");
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($invalidMethod, JSON_UNESCAPED_UNICODE);
    }
       
}
