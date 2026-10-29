<?php
// Sengaja dikosongkan — mencegah listing direktori & akses langsung.
http_response_code(403);
exit('Akses ditolak.');
