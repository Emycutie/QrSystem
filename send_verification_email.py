import sib_api_v3_sdk
from sib_api_v3_sdk.rest import ApiException

def send_verification_email(to_email, to_name, verification_link):
    """
    Sends a verification email via Brevo (Sendinblue)
    """
    api_key = "YOUR_BREVO_API_KEY"  # Replace with your actual API Key
    
    # Configure API key authorization
    configuration = sib_api_v3_sdk.Configuration()
    configuration.api_key['api-key'] = api_key
    api_instance = sib_api_v3_sdk.TransactionalEmailsApi(sib_api_v3_sdk.ApiClient(configuration))
    
    # Email content
    send_smtp_email = sib_api_v3_sdk.SendSmtpEmail(
        sender={"name": "Your Name", "email": "your-email@example.com"},
        to=[{"email": to_email, "name": to_name}],
        subject="Email Verification",
        html_content=f"""
            <p>Hello {to_name},</p>
            <p>Please click the link below to verify your email:</p>
            <p><a href='{verification_link}'>{verification_link}</a></p>
            <p>Thank you!</p>
        """
    )
    
    try:
        api_instance.send_transac_email(send_smtp_email)
        return "Email sent successfully!"
    except ApiException as e:
        return f"Error: {e}"

# Example Usage
to_email = "user@example.com"  # Replace with recipient's email
to_name = "User Name"
verification_link = "https://yourwebsite.com/verify?token=YOUR_UNIQUE_TOKEN"

print(send_verification_email(to_email, to_name, verification_link))
