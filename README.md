# Mysql to Simplified Triple format

This PHP script will take a MySQL database and convert it to a simple triple triple format in this format:

<http://db_string/user/1> <http://db_string/id> 1.
<http://db_string/user/1> <http://db_string/name> "Yourname".
<http://db_string/user/1> <http://db_string/role_id> <http://db_string/role/1>.

I'm playing with the idea of converting this simple script to Ruby and to make rake tasks out of this.
