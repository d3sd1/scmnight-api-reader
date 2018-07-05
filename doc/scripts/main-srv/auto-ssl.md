	certbot -d scmnight.com -d *.scmnight.com  --server https://acme-v02.api.letsencrypt.org/directory --manual --preferred-challenges dns certonly
	sudo service apache2 restart
	(crontab -l 2>/dev/null; echo "45 2 * * 6  certbot renew && service apache2 restart") | crontab -