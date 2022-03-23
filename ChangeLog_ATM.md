
## Backpoort Dolibarr V15.0
- FIX : Certificat mail pour office 365 
Backport de la conf de la 15.0 permettant de ne pas vérifier le certificat du server smtp
ajouter la conf caché **MAIN_MAIL_EMAIL_SMTP_ALLOW_SELF_SIGNED = 1**
cette conf et présente sur la page d'admin des mail de la V15
