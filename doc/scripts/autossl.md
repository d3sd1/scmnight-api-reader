git clone https://github.com/certbot/certbot.git
git checkout latest
./certbot-auto certbot --apache -d scmnight.com -d *.scmnight.com --agree-tos --manual-public-ip-logging-ok --preferred-challenges dns-01 --server https://acme-v02.api.letsencrypt.org/directory
sudo ls -l /etc/letsencrypt/live/scmnight.com
sudo service apache2 restart
(crontab -l 2>/dev/null; echo "45 2 * * 6  certbot renew && service apache2 restart") | crontab -