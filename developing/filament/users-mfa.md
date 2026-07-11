# Multi-Factor Auth (Filament v5)
**URL:** https://filamentphp.com/docs/5.x/users/multi-factor-authentication
### Enable: `->multiFactorAuthentication()` on panel.
### Setup flow: user scans QR code (TOTP), enters code to verify.
### Recovery codes: generated on setup, can be used once each.
### Email MFA: `->multiFactorAuthentication(using: MultiFactorAuthenticationProvider::email())`
### Customize: `getMultiFactorAuthenticationForm()` override.
