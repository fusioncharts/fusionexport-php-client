
<?php
use FusionExport\ExportManager;
use FusionExport\ExportConfig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Exporting a dashboard with header & footer options
require __DIR__ . '/../vendor/autoload.php';

// Instantiate the ExportConfig class and add the required configurations
$exportConfig = new ExportConfig();
$exportConfig->set('chartConfig', realpath(__DIR__ . '/resources/multiple.json'));
$exportConfig->set('templateFilePath', realpath(__DIR__ . '/resources/template.html'));
$exportConfig->set('type', 'pdf');
$exportConfig->set('headerEnabled', true);

// Instantiate the ExportManager class
$exportManager = new ExportManager();
// Call the export() method with the export config
$files = $exportManager->export($exportConfig, '.', true);
echo 'FusionExport PHP Client - Files generated, sending mail';

$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = 0;                                       
    $mail->isSMTP();                                            
    $mail->Host       = 'smtp.mailtrap.io';  
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = '9217733c3a014f';                     
    $mail->Password   = '39564c5d1ddd00';                               
    $mail->SMTPSecure = 'tls';                                  
    $mail->Port       = 587;                                    

    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('shreyas.a24@gmail.com');     

    foreach ($files as $index=>$file) {     
        $mail->addAttachment($file, "export$index.pdf"); 
    }

    $mail->isHTML(true);                                  
    $mail->Subject = 'FusionExport';
    $mail->Body    = 'Hello,<br><br>Kindly find the attachment of FusionExport exported files.<br><br>Thank you!';

    $mail->send();
    echo 'FusionExport PHP Client: mail sent.';
} catch (Exception $e) {
    echo "FusionExport PHP Client - error sending mail: {$mail->ErrorInfo}";
}