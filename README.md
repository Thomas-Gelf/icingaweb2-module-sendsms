SendSMS module for Icinga
=========================

This module provides a simple NotificationCommand for Icinga. It's solely purpose
is to send SMS notifications. It is possible to write hooks for additional SMS
providers. As of this writing, only one single implementation is available.

Configuration
-------------

You need to configure at least one account in `accounts.ini` of thi
cat /etc/icingaweb2/modules/sendsms/accounts.ini

```ini
[My GTX Account]
auth_key = "abcd1234-ef56-ab12-cd34-abcdef123456"
; provider = GtxMessaging ; NOT YET

; Proxy Settings:
; proxy = http://proxy.example.com:3128
; proxy_type = HTTP ; HTTP (default) or SOCKS5
; proxy_user = username
; proxy_pass = password
``` 

Usage
-----

    icingacli sendsms notification send \
      --account 'My GTX Account' \
      --to +491230000000 \
      --type PROBLEM \
      --host localhost \
      --service 'Some Service' \
      --output 'Alles kaputt' \
      --state DOWN \
      --sender 'Icinga'

Icinga Configuration
--------------------

    object NotificationCommand "sms-service-notification" {
        command = [ "/usr/bin/icingacli", "sendsms", "notification", "send" ]
        arguments += {
            "--account" = "$sms_account$"
            "--to"      = "$mobile_phone$"
            "--type"    = "$notification_type$"
            "--host"    = "$host.name$"
            "--service" = "$service.name$"
            "--output   = "$service.output$"
            "--state"   = "$service.state$"
            "--sender"  = "$sms_sender$"
        }

        vars.sms_sender = "Icinga"
    }