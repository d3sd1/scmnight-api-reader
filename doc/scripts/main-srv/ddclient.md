Setup automatic checker for new ip changes.

Test config:

    sudo ddclient -daemon=0 -debug -verbose -noquiet
    
    

Config file:
------
	# Configuration file for ddclient
	# /etc/ddclient.conf

	daemon=300
	ssl=yes
	syslog=yes
	mail=scmnight@gmail.com
	mail-failure=scmnight@gmail.com
	pid=/var/run/ddclient.pid
	use=web, web=dynamicdns.park-your-domain.com/getip

	protocol=namecheap
	server=dynamicdns.park-your-domain.com
	login=scmnight.com
	password=ef6247b670fc483b9a70617009a9ce05
	@,*,www,dev
----