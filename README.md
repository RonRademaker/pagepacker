# pagepacker
CLI Tool to pack a URL into a single HTML file

# state
Experimental

# usage
php bin/pagepacker.php convert:page <url to pack>

# description
The pagepacker packs a URL into a single HTML file. CSS will be downloaded and added as inline style. Images are downloaded and inserted into the HTML as base64. 

# use case
Easily create error pages you can use without requiring any external files. Useful when creating error pages for haproxy which can't load external files (beware of the max file size though).
