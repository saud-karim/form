# Contact Form with File Attachments

A complete web form solution that allows users to submit contact information with two image attachments and sends the data via email.

## Features

- **Responsive Design**: Built with Tailwind CSS for a modern, mobile-friendly interface
- **File Uploads**: Supports two image attachments with client and server-side validation
- **Email Integration**: Sends HTML-formatted emails with file attachments
- **Input Validation**: Comprehensive client and server-side validation and sanitization
- **Async Processing**: JavaScript Fetch API for seamless form submission without page reload
- **Security**: Input sanitization, file type validation, and CSRF protection

## Files Structure

```
contact-form/
├── index.html          # Frontend form with HTML, CSS, and JavaScript
├── send_email.php      # Backend PHP script for email processing
├── temp_uploads/       # Temporary directory for file uploads (created automatically)
└── README.md          # This file
```

## Requirements

- **Web Server**: Apache, Nginx, or any server supporting PHP
- **PHP**: Version 7.4 or higher
- **Extensions**: 
  - `fileinfo` (for MIME type detection)
  - `mail` (for email sending)
- **Permissions**: Write permissions for the `temp_uploads/` directory

## Setup Instructions

### 1. Web Server Setup

#### Option A: XAMPP (Local Development)
1. Download and install [XAMPP](https://www.apachefriends.org/)
2. Start Apache and MySQL services
3. Copy all files to `htdocs/contact-form/` directory
4. Access via `http://localhost/contact-form/`

#### Option B: Hosted Server
1. Upload all files to your web hosting directory
2. Ensure PHP is enabled on your hosting account
3. Set proper file permissions (755 for directories, 644 for files)

### 2. PHP Configuration

#### A. Update Email Settings in `send_email.php`

Edit the configuration array in `send_email.php`:

```php
$config = [
    'recipient_email' => 'your-email@example.com',    // Your email address
    'recipient_name' => 'Your Name',
    'sender_email' => 'noreply@yourdomain.com',       // Sender email (use your domain)
    'sender_name' => 'Website Contact Form',
    'subject' => 'New Contact Form Submission',
    'max_file_size' => 5 * 1024 * 1024,              // 5MB max file size
    'allowed_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
    'upload_dir' => 'temp_uploads/'
];
```

#### B. PHP Mail Configuration

**For Local Development (XAMPP):**

1. Edit `php.ini` file (usually in `xampp/php/php.ini`):
```ini
[mail function]
; For Windows
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com

; For Linux/Mac (using sendmail)
sendmail_path = /usr/sbin/sendmail -t -i
```

2. Restart Apache after making changes

**For Hosted Servers:**
- Most hosting providers have mail configured automatically
- Check with your hosting provider for specific SMTP settings
- Some providers require using their SMTP servers

#### C. Alternative: Using PHPMailer (Recommended for Production)

For better email delivery, consider using PHPMailer:

```bash
composer require phpmailer/phpmailer
```

Then update the email function in `send_email.php`:

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

function sendEmailWithPHPMailer($to, $subject, $body, $attachments) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com';
        $mail->Password   = 'your-app-password';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Recipients
        $mail->setFrom('noreply@yourdomain.com', 'Contact Form');
        $mail->addAddress($to);
        
        // Attachments
        foreach ($attachments as $attachment) {
            $mail->addAttachment($attachment['path'], $attachment['name']);
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
```

### 3. Security Considerations

#### File Upload Security
- Files are validated for type and size
- Temporary files are cleaned up after processing
- Upload directory should be outside web root in production

#### Production Settings
Update `send_email.php` for production:

```php
// Disable error display
error_reporting(0);
ini_set('display_errors', 0);

// Log errors instead
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');
```

#### Additional Security Measures
- Implement rate limiting to prevent spam
- Add CAPTCHA for bot protection
- Use HTTPS for secure file uploads
- Implement CSRF tokens for additional security

### 4. Customization

#### Styling
- Modify Tailwind classes in `index.html` for different styling
- Add custom CSS for advanced styling needs

#### Form Fields
- Add/remove fields by updating both HTML and PHP validation
- Update dropdown options in both files

#### File Types
- Modify `allowed_types` array in PHP configuration
- Update `accept` attribute in HTML file inputs

#### Email Template
- Customize the HTML email template in the `$emailContent` variable
- Add company branding, logos, or styling

## Testing

### Local Testing
1. Start your web server
2. Navigate to the form page
3. Fill out all required fields
4. Upload two test images
5. Submit the form
6. Check for success/error messages

### Email Testing
- Use a test email service like [MailHog](https://github.com/mailhog/MailHog) for local development
- Test with different email providers to ensure compatibility
- Verify attachments are received correctly

## Troubleshooting

### Common Issues

**"Failed to send email"**
- Check PHP mail configuration
- Verify SMTP settings
- Check server logs for detailed errors
- Ensure sender domain exists and is configured

**"File upload failed"**
- Check directory permissions (755 for temp_uploads/)
- Verify PHP upload settings in php.ini:
  ```ini
  file_uploads = On
  upload_max_filesize = 10M
  post_max_size = 12M
  max_file_uploads = 20
  ```

**"Form not submitting"**
- Check browser console for JavaScript errors
- Verify `send_email.php` path is correct
- Ensure server supports PHP

**"Images not received as attachments"**
- Check file size limits
- Verify MIME type detection is working
- Test with different image formats

### Debug Mode
Enable debug mode in `send_email.php`:

```php
// Add at the top of the file
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Add before mail sending
var_dump($attachments); // Check attachment data
```

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 16+

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

For issues and questions:
1. Check the troubleshooting section above
2. Review server error logs
3. Test with minimal configuration first
4. Ensure all requirements are met

## Version History

- **v1.0**: Initial release with basic functionality
- **v1.1**: Added comprehensive validation and error handling
- **v1.2**: Improved security and file handling
- **v1.3**: Enhanced email template and styling 