# w2ui-grid-serverside-php
w2ui grid serverside php script, swedish translation and contextmenu.

You will need 
http://w2ui.com/web/

And
https://jquery.com/

And for the contextmenu
https://swisnl.github.io/jQuery-contextMenu/

No sql provided, test on your own.
Supports join

if you dont want to use a where and join just leave these empty

//this is default sql where and group values, If you dont have any, leave empty
$sqlWhere  ="WHERE NOT colStatus ='2' AND NOT colStatus ='4' AND colKat ='0'";
$sqlGroup  ="GROUP BY ticketId, colTicketId";

This is not a proffessional script, just to get peaple like me going!
