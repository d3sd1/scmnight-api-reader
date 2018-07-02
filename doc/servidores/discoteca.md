SCM CENTRAL SERVER UBUNTU: (SIEMPRE WAN)
----
IP: 188.78.33.77
Puerto: 2022
User: administrador
Pass: e46936540O
User: root
pass: Q5KOavC2VzpI
-----
URL CON HTTPS:
- Se renueva mediante un cron de Let's Encrypt en el ordenador de sobremesa cada día a las 9AM. Si falla, revisar que mi ip pública y a la que apunta el dominio concuerden. 
- Para generar certificado pulsar N - 4 - linknetworks.es - C:\Users\Jause\Documents\SCMNIGHT\Projects\Web\scmweb\web 
- Se renueva por perfil público, pero el navegador resuelve la web como local (agregadas a hosts de windows 127.0.0.1).
- Se trata como si fuera una web local, aunque de cvara al público contiene mi ip pública.
-----
MAPEO URLS SCM
-----
scmnight.com -> desde aqui se permite visualizar el proyecto y exponerlo. tambien hay un link que permite acceder a la zona de clientes.
panel.scmnight.com -> URL local para las discotecas. si se accede desde la intranet apuntara al servidor principal de la distoteca. desde la extranet, apuntara a la administracion remota del servidor.
----
VERSIONES:
----
PHP: 7.2.0
Mysql: 5.7.20
Apache: 2.4.29