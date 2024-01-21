<?php

$servername = "";

$username = "";

$password = "";

$dbname = "";





$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {

  die("Грешка: " . $conn->connect_error);

}



// CAPTCHA

// $captcha = $_POST['g-recaptcha-response'];



// $captchaResponse = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6Le1Z88hAAAAAKeslt0vRkHK1dHHM1t86Giox-CM&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);



// $captchaReader = json_decode($captchaResponse, true);



// CAPTCHA



$url = "";

if ($_POST['serviceType'] == "checkGlobi") {
    $incrementValue = "UPDATE globalStats SET total_globi_checks = total_globi_checks + 1";
    $stmt = $conn->prepare($incrementValue);
    $stmt->execute();
    $stmt->close();

    if (isset($_POST['personalId']) && isset($_POST['licenseId'])) {
        $url = "https://e-uslugi.mvr.bg/api/Obligations/AND?obligatedPersonType=1&additinalDataForObligatedPersonType=1&mode=1&obligedPersonIdent=" . $_POST['personalId'] . "&drivingLicenceNumber=" . $_POST['licenseId'];
    }

    $opts = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
        ),
    );

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    $jsonData = json_decode($response, true);

    if ($response !== false) {
        foreach ($jsonData['obligationsData'] as $unitGroupData) {
            if ($unitGroupData['errorNoDataFound']) {
                echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
                    <i class="uil uil-times-circle"></i> Невалидни данни, моля опитайте отново.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
                    <i class="uil uil-times-circle"></i> Ако все пак смятате, че проблема не е от вас и въвеждате всичко правилно, моля свържете се с нас за да разгледаме потенциална грешка от наша страна.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
                exit;
            }
        }

        if (empty($jsonData['obligationsData'][0]['obligations'])) {
            echo '<div class="alert alert-success alert-icon alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle"></i> Не са открити невръчени глоби към шофьорска книжка №' . $_POST['licenseId'] .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            echo '<div class="alert alert-danger alert-icon alert-dismissible fade show" role="alert">
                <i class="uil uil-check-circle"></i> Открити са невръчени/неплатени глоби към шофьорска книжка №' . $_POST['licenseId'] .
                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';

            echo '<div class="table-responsive">';
            echo "<table class='table table-bordered'>";
            echo "<thead><tr><th>№ На задължение</th><th>Дата на издаване</th><th>Дължима сума</th><th>Сума с отстъпка</th><th>% Отстъпка</th><th>Име на глобеното лице</th><th>Информация</th><th>Серия</th></tr></thead>\n";
            echo "<tbody>\n";

            foreach ($jsonData['obligationsData'][0]['obligations'] as $obligation) {
                echo "<tr>\n";
                echo "<td>{$obligation['obligationID']}</td>\n";
                echo "<td>{$obligation['additionalData']['fishCreateDate']}</td>\n";
                echo "<td>{$obligation['amount']} ЛВ.</td>\n";
                echo "<td>{$obligation['discountAmount']} ЛВ.</td>\n";
                echo "<td>{$obligation['additionalData']['discount']}%</td>\n";
                echo "<td>{$obligation['obligedPersonName']}</td>\n";
                echo "<td>{$obligation['paymentReason']}</td>\n";
                echo "<td>{$obligation['additionalData']['documentSeries']}</td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>\n";
            echo "</table>\n";
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
            <i class="uil uil-times-circle"></i> Невалидни данни, моля опитайте отново.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
        echo '<div class="alert alert-warning alert-icon alert-dismissible fade show" role="alert">
            <i class="uil uil-times-circle"></i> Ако все пак смятате, че проблема не е от вас и въвеждате всичко правилно, моля свържете се с нас за да разгледаме потенциална грешка от наша страна.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>';
    }
}

?>

