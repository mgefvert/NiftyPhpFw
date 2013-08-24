This is a small example of a blogging application written in Nifty.

All the code specific to the blogging app resides in /app.blog, which illustrates
the possibility of using several applications in one web server space. Normally
the application would reside in /app. Take a look at the public/blog/index.php file
to see how the app is selected.

Copy the /sys code from the source directory, to make sure you have the
latest files. I might be lazy. 

/public is the web server root with a few images, css files and so on.

You'll have to create a MySQL database too and run mysql-tables.sql in it to create the tables
required for the app. Edit the settings in /app.blog/settings.conf to suit your particular needs.

Regards,
Mats Gefvert
