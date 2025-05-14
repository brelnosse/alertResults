<?php
// Inclure le fichier de configuration de la base de données
require_once __DIR__ . '/../vendor/autoload.php';

// Inclure les fichiers PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


/**
 * Classe utilitaire pour l'envoi d'emails avec PHPMailer
 */
class Mailer {
    /**
     * Envoie un email avec PHPMailer
     * 
     * @param string $to Adresse email du destinataire
     * @param string $subject Sujet de l'email
     * @param string $message Corps de l'email (HTML)
     * @param string $altMessage Corps de l'email (texte brut, optionnel)
     * @param array $attachments Pièces jointes (optionnel)
     * @param array $cc Destinataires en copie (optionnel)
     * @param array $bcc Destinataires en copie cachée (optionnel)
     * @param array $replyTo Adresse de réponse (optionnel)
     * @return bool True si l'email a été envoyé, false sinon
     */
    public static function sendEmail($to, $subject, $message, $altMessage = '', $attachments = [], $cc = [], $bcc = [], $replyTo = []) {
        // Charger la configuration
        $config = require __DIR__ . '/../config/mail_config.php';
        
        // Créer une nouvelle instance de PHPMailer
        $mail = new PHPMailer(true);
        
        try {
            // Configuration du serveur
            $mail->SMTPDebug = $config['debug']; // Niveau de débogage
            $mail->isSMTP(); // Utiliser SMTP
            $mail->Host = 'smtp.gmail.com'; // Serveur SMTP
            $mail->SMTPAuth = true; // Activer l'authentification SMTP
            $mail->Username = 'brelnosse2@gmail.com'; // Nom d'utilisateur SMTP
            $mail->Password = 'uomo yvbi igkh umte'; // Mot de passe SMTP
            $mail->SMTPSecure = 'tls'; // Encryption (tls/ssl)
            $mail->Port = 587; // Port SMTP
            
            // Encodage
            $mail->CharSet = 'UTF-8';
            
            // Destinataires
            $mail->setFrom("brelnosse2@gmail.com", 'brelnosse2@gmail.com');
            
            // Si $to est un tableau, ajouter chaque destinataire
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addAddress($name); // Si c'est un tableau indexé
                    } else {
                        $mail->addAddress($email, $name); // Si c'est un tableau associatif
                    }
                }
            } else {
                $mail->addAddress($to); // Ajouter un seul destinataire
            }
            
            // Ajouter les destinataires en copie
            if (!empty($cc)) {
                foreach ($cc as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addCC($name);
                    } else {
                        $mail->addCC($email, $name);
                    }
                }
            }
            
            // Ajouter les destinataires en copie cachée
            if (!empty($bcc)) {
                foreach ($bcc as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addBCC($name);
                    } else {
                        $mail->addBCC($email, $name);
                    }
                }
            }
            
            // Ajouter les adresses de réponse
            if (!empty($replyTo)) {
                foreach ($replyTo as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addReplyTo($name);
                    } else {
                        $mail->addReplyTo($email, $name);
                    }
                }
            }
            
            // Ajouter les pièces jointes
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $mail->addAttachment(
                            $attachment['path'],
                            $attachment['name'] ?? '',
                            $attachment['encoding'] ?? 'base64',
                            $attachment['type'] ?? '',
                            $attachment['disposition'] ?? 'attachment'
                        );
                    } else {
                        $mail->addAttachment($attachment);
                    }
                }
            }
            
            // Contenu de l'email
            $mail->isHTML(true); // Format HTML
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            // Ajouter une version texte brut si fournie
            if (!empty($altMessage)) {
                $mail->AltBody = $altMessage;
            } else {
                // Générer automatiquement une version texte brut à partir du HTML
                $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $message));
            }
            
            // Envoyer l'email
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Journaliser l'erreur
            error_log('Erreur lors de l\'envoi de l\'email: ' . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Envoie un email de réinitialisation de mot de passe
     * 
     * @param string $to Adresse email du destinataire
     * @param string $token Token de réinitialisation
     * @param string $userType Type d'utilisateur (admin, student, teacher)
     * @param string $firstname Prénom de l'utilisateur
     * @param string $lastname Nom de l'utilisateur
     * @return bool True si l'email a été envoyé, false sinon
     */
    public static function sendPasswordResetEmail($to, $token, $userType, $firstname, $lastname) {
        // Déterminer le chemin du portail en fonction du type d'utilisateur
        $portalPath = '';
        switch ($userType) {
            case 'admin':
                $portalPath = 'ad';
                break;
            case 'student':
                $portalPath = 'student';
                break;
            case 'teacher':
                $portalPath = 'teacher';
                break;
        }
        
        // Construire l'URL de réinitialisation
        $resetUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/' . $portalPath . '/views/reset_password_view.php?token=' . $token;
        
        // Sujet de l'email
        $subject = 'Réinitialisation de votre mot de passe - AlertResults';
        
        // Corps de l'email en HTML
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Réinitialisation de mot de passe</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #004080;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .content {
                    padding: 0 20px;
                }
                .button {
                    display: inline-block;
                    background-color: #004080;
                    color: white;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .footer {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Réinitialisation de mot de passe</h2>
                </div>
                <div class="content">
                    <p>Bonjour ' . htmlspecialchars($firstname . ' ' . $lastname) . ',</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe pour votre compte AlertResults.</p>
                    <p>Veuillez cliquer sur le bouton ci-dessous pour réinitialiser votre mot de passe :</p>
                    <p><a href="' . $resetUrl . '" class="button" style="color: white">Réinitialiser mon mot de passe</a></p>
                    <p>Si vous n\'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email.</p>
                    <p>Ce lien expirera dans 1 heure pour des raisons de sécurité.</p>
                    <p>Cordialement,<br>L\'équipe AlertResults</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Version texte brut de l'email
        $altMessage = "Bonjour " . $firstname . " " . $lastname . ",\n\n" .
            "Vous avez demandé la réinitialisation de votre mot de passe pour votre compte AlertResults.\n\n" .
            "Veuillez cliquer sur le lien ci-dessous pour réinitialiser votre mot de passe :\n" .
            $resetUrl . "\n\n" .
            "Si vous n'avez pas demandé cette réinitialisation, vous pouvez ignorer cet email.\n\n" .
            "Ce lien expirera dans 1 heure pour des raisons de sécurité.\n\n" .
            "Cordialement,\nL'équipe AlertResults";
        
        // Envoyer l'email
        return self::sendEmail($to, $subject, $message, $altMessage);
    }
    
    /**
     * Envoie un email de confirmation de changement de mot de passe
     * 
     * @param string $to Adresse email du destinataire
     * @param string $firstname Prénom de l'utilisateur
     * @param string $lastname Nom de l'utilisateur
     * @return bool True si l'email a été envoyé, false sinon
     */
    public static function sendPasswordChangedEmail($to, $firstname, $lastname) {
        // Sujet de l'email
        $subject = 'Confirmation de changement de mot de passe - AlertResults';
        
        // Corps de l'email en HTML
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Confirmation de changement de mot de passe</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .header {
                    background-color: #004080;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px 5px 0 0;
                    margin-bottom: 20px;
                }
                .content {
                    padding: 0 20px;
                }
                .footer {
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #ddd;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Confirmation de changement de mot de passe</h2>
                </div>
                <div class="content">
                    <p>Bonjour ' . htmlspecialchars($firstname . ' ' . $lastname) . ',</p>
                    <p>Nous vous confirmons que votre mot de passe a été modifié avec succès.</p>
                    <p>Si vous n\'êtes pas à l\'origine de cette modification, veuillez contacter immédiatement notre support.</p>
                    <p>Cordialement,<br>L\'équipe AlertResults</p>
                </div>
                <div class="footer">
                    <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Version texte brut de l'email
        $altMessage = "Bonjour " . $firstname . " " . $lastname . ",\n\n" .
            "Nous vous confirmons que votre mot de passe a été modifié avec succès.\n\n" .
            "Si vous n'êtes pas à l'origine de cette modification, veuillez contacter immédiatement notre support.\n\n" .
            "Cordialement,\nL'équipe AlertResults";
        
        // Envoyer l'email
        return self::sendEmail($to, $subject, $message, $altMessage);
    }
        /**
     * Envoie un email de confirmation d'inscription
     * 
     * @param string $to L'adresse email du destinataire
     * @param string $firstname Le prénom du destinataire
     * @param string $lastname Le nom du destinataire
     * @param string $userType Le type d'utilisateur (student, teacher, admin)
     * @return bool True si l'email a été envoyé, false sinon
     */
    public function sendRegistrationEmail($to, $firstname, $lastname, $userType) {
        // Destinataire
        $subject = 'Confirmation de votre inscription - AlertResults';            
        // Adapter le message selon le type d'utilisateur
        $userTypeLabel = '';
        $additionalInfo = '';
        
        switch ($userType) {
            case 'student':
                $userTypeLabel = 'étudiant';
                $additionalInfo = '<p>Votre compte doit être validé par un administrateur avant que vous puissiez vous connecter. Vous recevrez un email lorsque votre compte sera validé.</p>';
                break;
            case 'teacher':
                $userTypeLabel = 'enseignant';
                break;
            case 'admin':
                $userTypeLabel = 'administrateur';
                break;
        }
        
        // Corps du message HTML
        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #4a5568;'>Confirmation de votre inscription</h2>
                <p>Bonjour {$firstname} {$lastname},</p>
                <p>Votre compte {$userTypeLabel} a été créé avec succès.</p>
                {$additionalInfo}
                <p>Vous pouvez maintenant vous connecter en utilisant votre adresse email et le mot de passe que vous avez défini.</p>
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        ";
        
        // Version texte brut
        $altMessage = "
            Confirmation de votre inscription
            
            Bonjour {$firstname} {$lastname},
            
            Votre compte {$userTypeLabel} a été créé avec succès.
            " . ($userType === 'student' ? "Votre compte doit être validé par un administrateur avant que vous puissiez vous connecter. Vous recevrez un email lorsque votre compte sera validé." : "") . "
            
            Vous pouvez maintenant vous connecter en utilisant votre adresse email et le mot de passe que vous avez défini.
            
            Cordialement,
            L'équipe administrative
        ";
        
        return self::sendEmail($to, $subject, $message, $altMessage);
    }
        /**
     * Envoie un email d'approbation de compte à l'étudiant
     * 
     * @param string $to L'adresse email de l'étudiant
     * @param string $firstname Le prénom de l'étudiant
     * @param string $lastname Le nom de l'étudiant
     * @return bool True si l'email a été envoyé, false sinon
     */
    public function sendAccountApprovalEmail($to, $firstname, $lastname) {
        // Destinataire
        $subject = 'Votre compte a été validé';
        
        // Corps du message HTML
        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #4a5568;'>Votre compte a été validé</h2>
                <p>Bonjour {$firstname} {$lastname},</p>
                <p>Nous avons le plaisir de vous informer que votre compte étudiant a été validé par notre équipe administrative.</p>
                <p>Vous pouvez maintenant vous connecter à votre espace en utilisant votre adresse email et votre mot de passe.</p>
                <p style='margin: 20px 0;'>
                    <a href='http://alertResults.test/student/' style='background-color: #3490dc; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Se connecter</a>
                </p>
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        ";
        
        // Version texte brut
        $altMessage = "
            Votre compte a été validé
            
            Bonjour {$firstname} {$lastname},
            
            Nous avons le plaisir de vous informer que votre compte étudiant a été validé par notre équipe administrative.
            
            Vous pouvez maintenant vous connecter à votre espace en utilisant votre adresse email et votre mot de passe.
            
            http://alertResults.test/student/
            
            Cordialement,
            L'équipe administrative
        ";
        
        return self::sendEmail($to, $subject, $message, $altMessage);
    }
    function sendNewStudentNotification(
        $to,
        $name,
        $firstname,
        $lastname,
        $specialite,
        $email
    ) {
        // Destinataire
        $subject = 'Nouvel étudiant inscrit';
        
        // Corps du message HTML
        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #4a5568;'>Nouvel étudiant inscrit</h2>
                <p>Bonjour {$name} {$lastname},</p>
                <p>Nous avons le plaisir de vous informer qu'un nouvel étudiant a été inscrit dans votre spécialité.</p>
                <p><strong>Nom :</strong> {$firstname} {$lastname}</p>
                <p><strong>Spécialité :</strong> {$specialite}</p>
                <p><strong>Email :</strong> {$email}</p>
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        ";
        
        // Version texte brut
        $altMessage = "
            Nouvel étudiant inscrit
            
            Bonjour {$name} {$lastname},
            
            Nous avons le plaisir de vous informer qu'un nouvel étudiant a été inscrit dans votre spécialité.
            
            Nom : {$firstname} {$lastname}
            Spécialité : {$specialite}
            Email : {$email}
            
            Cordialement,
            L'équipe administrative
        ";
        
        return self::sendEmail($to, $subject, $message, $altMessage);
    }
    /**
     * Envoie un email de rejet de compte à l'étudiant
     * 
     * @param string $to L'adresse email de l'étudiant
     * @param string $firstname Le prénom de l'étudiant
     * @param string $lastname Le nom de l'étudiant
     * @param string $reason La raison du rejet
     * @return bool True si l'email a été envoyé, false sinon
     */
    public function sendAccountRejectionEmail($to, $firstname, $lastname, $reason) {
        $subject = 'Information concernant votre compte';
        
        // Corps du message HTML
        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #4a5568;'>Information concernant votre compte</h2>
                <p>Bonjour {$firstname} {$lastname},</p>
                <p>Nous vous informons que votre demande de création de compte étudiant n'a pas été approuvée.</p>
                <p><strong>Raison :</strong> {$reason}</p>
                <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus d'informations, veuillez contacter l'administration de l'établissement.</p>
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        ";
        
        // Version texte brut
        $altMessage = "
            Information concernant votre compte
            
            Bonjour {$firstname} {$lastname},
            
            Nous vous informons que votre demande de création de compte étudiant n'a pas été approuvée.
            
            Raison : {$reason}
            
            Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus d'informations, veuillez contacter l'administration de l'établissement.
            
            Cordialement,
            L'équipe administrative
        ";
        return self::sendEmail($to, $subject, $message, $altMessage); 

    }
        /**
     * Envoie un email de rejet de compte à l'enseignant
     * 
     * @param string $to L'adresse email de l'enseignant
     * @param string $firstname Le prénom de l'enseignant
     * @param string $lastname Le nom de l'enseignant
     * @param string $reason La raison du rejet
     * @return bool True si l'email a été envoyé, false sinon
     */
    public function sendTeacherAccountRejectionEmail($to, $firstname, $lastname, $reason) {
        $subject = 'Information concernant votre compte';
        
        // Corps du message HTML
        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #4a5568;'>Information concernant votre compte</h2>
                <p>Bonjour {$firstname} {$lastname},</p>
                <p>Nous vous informons que votre demande pour enseigner les matières que vous avez sélèctionner n'a pas été approuvée.</p>
                <p><strong>Raison :</strong> {$reason}</p>
                <p>Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus d'informations, veuillez contacter l'administration de l'établissement.</p>
                <p>Vous pouvez consulter la liste des matières que vous avez demandées dans votre espace enseignant.</p>
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        ";
        
        // Version texte brut
        $altMessage = "
            Information concernant votre compte
            
            Bonjour {$firstname} {$lastname},
            
            Nous vous informons que votre demande pour enseigner les matières que vous avez sélèctionner n'a pas été approuvée.
            
            Raison : {$reason}
            
            Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez obtenir plus d'informations, veuillez contacter l'administration de l'établissement.
            
            Cordialement,
            L'équipe administrative
        ";
        return self::sendEmail($to, $subject, $message, $altMessage); 

    }

            /**
     * Envoie un email d'approbation de compte à l'étudiant
     * 
     * @param string $to L'adresse email de l'étudiant
     * @param string $firstname Le prénom de l'étudiant
     * @param string $lastname Le nom de l'étudiant
     * @return bool True si l'email a été envoyé, false sinon
     */
    public function sendTeacherAccountApprovalEmail($to, $firstname, $lastname) {
        // Destinataire
        $subject = 'Votre compte a été validé';
        
        // Corps du message HTML
        $message = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #4a5568;'>Votre compte a été validé</h2>
                <p>Bonjour {$firstname} {$lastname},</p>
                <p>Nous avons le plaisir de vous informer que les matières sélèctionnées ont été validé par notre équipe administrative.</p>
                <p>Vous pouvez maintenant vous connecter à votre espace en utilisant votre adresse email et votre mot de passe, pour commençer le remplissage de notes.</p>
                <p style='margin: 20px 0;'>
                    <a href='http://alertResults.test/teacher/' style='background-color: #3490dc; color: white; padding: 10px 15px; text-decoration: none; border-radius: 4px;'>Se connecter</a>
                </p>
                <p>Cordialement,<br>L'équipe administrative</p>
            </div>
        ";
        
        // Version texte brut
        $altMessage = "
            Votre compte a été validé
            
            Bonjour {$firstname} {$lastname},
            
                Nous avons le plaisir de vous informer que les matières sélèctionnées ont été validé par notre équipe administrative.
                Vous pouvez maintenant vous connecter à votre espace en utilisant votre adresse email et votre mot de passe, pour commençer le remplissage de notes.
            
            http://alertResults.test/teacher/
            
            Cordialement,
            L'équipe administrative
        ";
        
        return self::sendEmail($to, $subject, $message, $altMessage);
    }
}
?>

