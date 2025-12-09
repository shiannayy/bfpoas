/**
 * Send email using AJAX
 * @param {string} subject - Email subject
 * @param {string|Array} receiverEmail - Recipient email address(es) - string (comma/semicolon separated) or array
 * @param {string} messageContent - Email message content
 * @param {File} [attachment=null] - Optional file attachment
 * @param {function} [callback=null] - Optional callback function
 * @returns {Promise} - Returns a promise for async handling
 */
function sendEmail(subject, receiverEmail, messageContent, attachment = null, callback = null) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        
        // PROCESS EMAIL ADDRESSES
        let emails = [];
        
        if (Array.isArray(receiverEmail)) {
            // Already an array
            emails = receiverEmail.filter(email => email && email.trim());
        } else if (typeof receiverEmail === 'string') {
            // Split string by comma or semicolon
            emails = receiverEmail.split(/[;,]/)
                .map(email => email.trim())
                .filter(email => email.length > 0);
        }
        
        // Validate we have emails
        if (emails.length === 0) {
            reject(new Error('No email addresses provided'));
            showMessage('✗ No email addresses provided', 'error');
            return;
        }
        
        // Add emails to FormData as array
        emails.forEach(email => {
            formData.append('to_email[]', email);
        });
        
        formData.append('subject', subject);
        formData.append('message', messageContent);
        
        // Handle attachment
        if (attachment) {
            if (attachment instanceof File || attachment instanceof Blob) {
                if (attachment instanceof Blob && !attachment.name) {
                    const filename = 'attachment_' + Date.now() + '.pdf';
                    attachment = new File([attachment], filename, { 
                        type: attachment.type || 'application/pdf' 
                    });
                }
                formData.append('attachment', attachment);
            }
        }
        
        // Show loading state
        if (window.showLoading) {
            window.showLoading('Sending email...');
        }
        
        // Send AJAX request
        $.ajax({
            url: '../includes/send_mail.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (window.hideLoading) {
                    window.hideLoading();
                }
                
                resolve(response);
                
                if (callback && typeof callback === 'function') {
                    callback(response);
                }
                
                if (response.success) {
                    showMessage('✓ ' + response.message, 'success');
                         // Cleanup the PDF file if attachment exists
                        if (attachment && attachment.name) {
                            $.post('../includes/cleanup_pdf.php', {
                                filename: attachment.name
                            }).done(function(cleanupResponse) {
                                console.log('Cleanup result:', cleanupResponse);
                            });
                        }
                } else {
                    showMessage('✗ ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                if (window.hideLoading) {
                    window.hideLoading();
                }
                
                const errorMessage = 'AJAX Error: ' + error;
                reject(new Error(errorMessage));
                showMessage('✗ ' + errorMessage, 'error');
                console.error('Email sending failed:', error);
            }
        });
    });
}

/**
 * Helper function to show messages
 * @param {string} message - Message to display
 * @param {string} type - Message type (success, error, warning)
 */
function showMessage(message, type = 'info') {
    $('.email-message').remove();
    
    const messageDiv = $('<div class="email-message"></div>')
        .addClass(type)
        .html(message)
        .css({
            'padding': '10px',
            'margin': '10px 0',
            'border-radius': '4px',
            'display': 'block'
        });
    
    switch(type) {
        case 'success':
            messageDiv.css({
                'background-color': '#d4edda',
                'color': '#155724',
                'border': '1px solid #c3e6cb'
            });
            break;
        case 'error':
            messageDiv.css({
                'background-color': '#f8d7da',
                'color': '#721c24',
                'border': '1px solid #f5c6cb'
            });
            break;
        default:
            messageDiv.css({
                'background-color': '#d1ecf1',
                'color': '#0c5460',
                'border': '1px solid #bee5eb'
            });
    }
    
    $('body').prepend(messageDiv);
    
    if (type === 'success') {
        setTimeout(() => {
            messageDiv.fadeOut(500, function() {
                $(this).remove();
            });
        }, 5000);
    }
}