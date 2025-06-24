<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

class ContactForm {
    private $recipient;
    private $fromName;
    private $fromEmail;

    public function __construct($recipient, $fromName, $fromEmail) {
        $this->recipient = $recipient;
        $this->fromName = $fromName;
        $this->fromEmail = $fromEmail;
    }

    public function sendEmail($name, $email, $phone, $subject, $message) {
        // Validate required fields
        if (empty($name) || empty($email) || empty($message)) {
            http_response_code(400);
            echo "Please fill in all required fields (Name, Email, and Message).";
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo "Please enter a valid email address.";
            return;
        }

        $email_content = $this->buildEmailContent($name, $email, $phone, $subject, $message);
        $email_headers = $this->buildEmailHeaders($email);
        
        // Use a more descriptive subject if none provided
        $email_subject = !empty($subject) ? $subject : "New Contact Form Submission from " . $name;

        // For testing purposes - comment out mail() and show data instead
        // Uncomment the mail() line below when you have SMTP configured
        
        /*
        if (mail($this->recipient, $email_subject, $email_content, $email_headers)) {
            http_response_code(200);
            echo "Thank You! Your message has been sent successfully.";
        } else {
            http_response_code(500);
            echo "Oops! Something went wrong and we couldn't send your message. Please try again later.";
        }
        */
        
        // TEST VERSION - Shows form data instead of sending email
        http_response_code(200);
        echo "<h3>Form submitted successfully!</h3>";
        echo "<p><strong>To:</strong> " . $this->recipient . "</p>";
        echo "<p><strong>Subject:</strong> " . htmlspecialchars($email_subject) . "</p>";
        echo "<h4>Email Content:</h4>";
        echo "<pre>" . htmlspecialchars($email_content) . "</pre>";
        echo "<p><em>Note: This is a test mode. No actual email was sent.</em></p>";
    }

    private function buildEmailContent($name, $email, $phone, $subject, $message) {
        $content = "New Contact Form Submission\n";
        $content .= "================================\n\n";
        
        $fields = array(
            "Name" => $name,
            "Email" => $email,
            "Phone" => $phone,
            "Subject" => $subject,
            "Message" => $message
        );
        
        foreach ($fields as $fieldName => $fieldValue) {
            if (!empty($fieldValue)) {
                $content .= "$fieldName: $fieldValue\n\n";
            }
        }
        
        $content .= "--------------------------------\n";
        $content .= "Sent on: " . date('Y-m-d H:i:s') . "\n";
        $content .= "IP Address: " . $_SERVER['REMOTE_ADDR'] . "\n";
        
        return $content;
    }

    private function buildEmailHeaders($replyEmail) {
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: $replyEmail\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        return $headers;
    }
}

// Configuration
$recipient = "ptwotechnologies@gmail.com ";
$fromName = "Contact Form";
$fromEmail = "noreply@yourdomain.com"; // Change this to your domain

$contactForm = new ContactForm($recipient, $fromName, $fromEmail);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = strip_tags(trim($_POST["name"]));
    $name = str_replace(array("\r","\n"),array(" "," "),$name);
    
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    
    $phone = strip_tags(trim($_POST["phone"]));
    $phone = str_replace(array("\r","\n"),array(" "," "),$phone);
    
    $subject = strip_tags(trim($_POST["subject"]));
    $subject = str_replace(array("\r","\n"),array(" "," "),$subject);
    
    $message = strip_tags(trim($_POST["textarea"]));

    // Send the email
    $contactForm->sendEmail($name, $email, $phone, $subject, $message);
    
} else {
    http_response_code(405);
    echo "Method not allowed. Please submit the form properly.";
}

?>