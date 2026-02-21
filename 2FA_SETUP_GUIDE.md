# Two-Factor Authentication (2FA) Setup Guide

## Overview
This application now supports Two-Factor Authentication (2FA) using Google Authenticator to add an extra layer of security to user accounts.

## Features
✅ Google Authenticator integration
✅ QR Code scanning for easy setup
✅ Manual secret key entry option
✅ User-controlled enable/disable
✅ Session-based verification
✅ Clean verification challenge page

## Requirements
- Google Authenticator app (iOS/Android) or any compatible TOTP authenticator app:
  - Google Authenticator
  - Microsoft Authenticator
  - Authy
  - 1Password
  - etc.

## How to Enable 2FA for Your Account

### Step 1: Access 2FA Settings
1. Log in to your account at `/admin`
2. Click on "2FA Settings" in the navigation menu (shield icon)

### Step 2: Set Up Google Authenticator
1. **Download the App** (if you haven't already):
   - iOS: Download from App Store
   - Android: Download from Google Play Store

2. **Scan the QR Code**:
   - Open Google Authenticator app
   - Tap the "+" icon to add a new account
   - Select "Scan a QR code"
   - Scan the QR code displayed on the screen

3. **Alternative - Manual Entry**:
   - If you can't scan the QR code, use manual entry
   - Copy the secret key displayed below the QR code
   - In Google Authenticator, select "Enter a setup key"
   - Enter your email and paste the secret key

### Step 3: Verify and Enable
1. Enter the 6-digit code shown in Google Authenticator app
2. Click "Enable 2FA" button
3. You'll see a success message confirming 2FA is enabled

## How to Log In with 2FA

### Regular Login Process:
1. Go to `/admin` and enter your email and password
2. After successful password authentication, you'll be redirected to the 2FA challenge page
3. Open Google Authenticator app on your phone
4. Enter the 6-digit code for this account
5. The code auto-submits when 6 digits are entered
6. You'll be logged in and redirected to the dashboard

### Important Notes:
- The verification code changes every 30 seconds
- If a code expires, just wait for the next one
- You have a small time window (tolerance) for code acceptance

## How to Disable 2FA

### If You Want to Turn Off 2FA:
1. Navigate to "2FA Settings" in the navigation menu
2. Enter your current 6-digit verification code
3. Click "Disable 2FA"
4. Confirm the action

⚠️ **Warning**: Disabling 2FA makes your account less secure!

## Troubleshooting

### "Invalid Code" Error
- **Cause**: Code expired or incorrect time sync
- **Solution**: 
  - Wait for the next code (they refresh every 30 seconds)
  - Check your phone's time settings (must be set to automatic)
  - Make sure you're using the latest code

### Lost Access to Authenticator App
- **Solution**: Contact your system administrator to disable 2FA for your account
- **Prevention**: Save your backup codes (if implemented) or secret key in a secure location

### QR Code Not Displaying
- **Solution**: Use the manual entry method with the secret key displayed below the QR code

### Already Verified but Asked Again
- **Cause**: Session expired or browser cleared
- **Solution**: Enter your code again to re-verify

## Security Best Practices

1. **Keep Your Phone Secure**
   - Use a lock screen on your phone
   - Don't share your phone with others

2. **Backup Your Authenticator**
   - Most authenticator apps support cloud backup
   - Write down the secret key in a secure location
   - Consider using a password manager

3. **Device Management**
   - Only add the account to devices you control
   - Remove old devices from your authenticator app

4. **Regular Updates**
   - Keep your authenticator app updated
   - Ensure your phone's OS is up to date

## For Administrators

### Database Schema
The following columns were added to the `users` table:
- `google2fa_secret` (text, nullable, encrypted): Stores the 2FA secret key
- `google2fa_enabled` (boolean, default false): Indicates if 2FA is active
- `google2fa_enabled_at` (timestamp, nullable): When 2FA was enabled

### Routes Added
- `GET /2fa/challenge` - Displays the 2FA verification page
- `POST /2fa/verify` - Verifies the 2FA code
- `GET /2fa/logout` - Logs out and clears 2FA session

### Middleware
- `TwoFactorAuthentication` middleware checks for 2FA verification on protected routes
- Integrated with Filament's auth middleware

### Forcing 2FA for Users
To make 2FA mandatory for all users, you can:
1. Modify the middleware to require 2FA for all authenticated users
2. Add a database migration to set `google2fa_enabled` to true for all users
3. Generate and securely distribute secret keys to users

## Testing

### Test the 2FA Flow:
1. Create a test user account
2. Log in and navigate to 2FA Settings
3. Enable 2FA using Google Authenticator
4. Log out completely
5. Log back in and verify you're prompted for 2FA code
6. Enter the code and confirm access is granted

## Technical Details

### Package Used
- `pragmarx/google2fa-laravel` - Google2FA Laravel integration
- `pragmarx/google2fa-qrcode` - QR code generation

### Code Flow
1. User logs in with email/password (Filament authentication)
2. `TwoFactorAuthentication` middleware checks if user has 2FA enabled
3. If enabled and not verified, redirect to `/2fa/challenge`
4. User enters code from authenticator app
5. Code is verified against stored secret
6. On success, session flag `2fa_verified` is set
7. User is redirected to intended page
8. On logout, `2fa_verified` session flag is cleared

### Security Features
- Secrets are encrypted in database
- Time-based one-time passwords (TOTP)
- Window tolerance for clock drift
- Session-based verification (not cookie-based)
- CSRF protection on all forms

## Future Enhancements (Optional)

Consider implementing:
- [ ] Backup codes for account recovery
- [ ] SMS fallback option
- [ ] Email-based 2FA alternative
- [ ] Remember this device for 30 days
- [ ] Force 2FA for admin roles
- [ ] 2FA audit log
- [ ] Multiple device support

## Support

For issues or questions about 2FA:
1. Check this documentation first
2. Contact your system administrator
3. Review error logs at `storage/logs/laravel.log`

---

**Last Updated**: February 21, 2026
**Version**: 1.0.0
