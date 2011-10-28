# Mysql to Simplified Triple format

This PHP script will take a MySQL database and convert it to a simple triple triple format in this format:

&lt;http://db_string/user/1&gt; &lt;http://db_string/id&gt; 1.
&lt;http://db_string/user/1&gt; &lt;http://db_string/name&gt; "Yourname".
&lt;http://db_string/user/1&gt; &lt;http://db_string/role_id&gt; &lt;http://db_string/role/1&gt;.

I'm playing with the idea of converting this simple script to Ruby and to make rake tasks out of this.
