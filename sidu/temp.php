<?php
include 'inc.page.php';
@uppe();
@main();
@down();

function main(){
	echo "<div class='web'><p><b>SQL Easy Templates</b></p>
<p>MySQL:<br>EXPLAIN SELECT * FROM tab<br>KILL process-ID</p>
<p>PostgreSQL:<br>EXPLAIN ANALYZE SELECT * FROM tab<br>SELECT pg_cancel_backend(procpid or PID after pg9.2)</p>

<p>SET PASSWORD = password('new-pass')
<br>SET PASSWORD for user@localhost = password('new-pass')</p>
<p>[my]select inet_aton('127.0.0.1')==[pg]inetmi('127.0.0.1','0.0.0.0')
<br>[my]inet_ntoa()==[pg]'0.0.0.0'::inet+int
<br>select substring_index('sidu@yahoo.com','@',1)</p>
<p>It's a good idea to have primary key in each table
<br>It's a good idea to have int col ahead, and blob col at end
<br>SIDU will sort first col if no sort found by default</p>

<p>sudo apt-get install apache2 php5 libapache2-mod-php5
<br>sudo apt-get install mysql-server mysql-client php5-mysql
<br>sudo apt-get install php5-gd</p>

<p>sudo apt-get install postgresql
<br>sudo apt-get install php5-pgsql
<br>sudo -u postgres psql ## login via cmd
<br>\password postgres ## change password</p>

<p>sudo /etc/init.d/postgresql restart
<br>sudo /etc/init.d/apache2 restart</p>

<p>www.mysql.com<br>www.postgresql.org<br>www.sqlite.org<br>www.cubrid.org</p>
<p>This easy temp will be sorted in next release</p>

<pre>CREATE TABLE sidu_fk (
  tab varchar(80) NOT NULL,
  col varchar(80) NOT NULL,
  ref_tab varchar(80) NOT NULL,
  ref_cols varchar(255) NOT NULL,
  where_sort varchar(255),
  PRIMARY KEY (tab,col)
);\t//you need refresh window after sidu_fk table is created</pre>
</div>";
}
?>
